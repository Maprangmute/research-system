// forecast.js — Node-only simple forecasters (run on Plesk)
import mysql from "mysql2/promise";

// ===== DB Config =====
const DB = {
  host: process.env.MYSQL_HOST || "127.0.0.1",
  port: Number(process.env.MYSQL_PORT || 3306),
  user: process.env.MYSQL_USER || "root",
  password: process.env.MYSQL_PASSWORD || "login165930",
  database: process.env.MYSQL_DB || "sihpwork_main",
  waitForConnections: true,
  connectionLimit: 10,
  charset: "utf8mb4_general_ci",
};
const mysqlPool = mysql.createPool(DB);

// ===== helpers =====
const clamp = (x, lo = 0, hi = 1) => Math.max(lo, Math.min(hi, x));
const sigmoid = (z) => 1 / (1 + Math.exp(-z));
function emaForecast(series, alpha = 0.4, horizon = 30) {
  if (!Array.isArray(series) || !series.length) return Array(horizon).fill(0);
  let ema = series[0];
  for (let i = 1; i < series.length; i++) ema = alpha * series[i] + (1 - alpha) * ema;
  return Array.from({ length: horizon }, () => ema);
}
function zscore(x, mean, std) {
  return std > 0 ? (x - mean) / std : 0;
}

// ===== 1) FTE Overrun risk (next month) =====
export async function forecastFTEOverrun({ month = null } = {}) {
  const conn = await mysqlPool.getConnection();
  try {
    const [rows] = await conn.query(`
      SELECT person_id,
             DATE_FORMAT(start_date, '%Y-%m') AS ym,
             SUM(fte_value) AS fte
      FROM fte_roles
      GROUP BY person_id, ym
      ORDER BY person_id, ym
    `);
    if (!rows.length) return { ok: false, error: "No FTE data" };

    const byPerson = new Map();
    for (const r of rows) {
      const id = String(r.person_id);
      if (!byPerson.has(id)) byPerson.set(id, []);
      byPerson.get(id).push({ ym: r.ym, fte: Number(r.fte || 0) });
    }

    const risky_people = [];
    let scores = [];
    for (const [pid, arr] of byPerson.entries()) {
      arr.sort((a, b) => a.ym.localeCompare(b.ym));
      const values = arr.map((a) => a.fte);
      const last = values.at(-1) ?? 0;
      const mean = values.reduce((s, v) => s + v, 0) / values.length;
      const std = Math.sqrt(values.reduce((s, v) => s + (v - mean) * (v - mean), 0) / values.length);
      const trend = values.length >= 2 ? values.at(-1) - values.at(-2) : 0;
      const raw = 1.2 * last + 0.6 * Math.max(0, trend) + 0.4 * zscore(last, mean, std);
      const prob = clamp(sigmoid(raw - 1.0));
      scores.push(prob);
      if (last > 0.9 || prob > 0.5) risky_people.push(pid);
    }

    const risk = scores.length ? scores.reduce((a, b) => a + b, 0) / scores.length : 0;
    return { ok: true, risk, risky_people };
  } finally {
    conn.release();
  }
}

// ===== 2) IRB expiry count forecast (daily) =====
export async function forecastIRBExpiry({ horizon_days = 90 } = {}) {
  const conn = await mysqlPool.getConnection();
  try {
    const [rows] = await conn.query(`
      SELECT expiry_date AS d, COUNT(*) AS c
      FROM irb
      WHERE expiry_date IS NOT NULL
      GROUP BY d
      ORDER BY d
    `);
    if (!rows.length) return { ok: false, error: "No IRB data" };

    const series = rows.map((r) => Number(r.c || 0));
    const forecast = emaForecast(series, 0.4, horizon_days).map((y, i) => ({
      day: i + 1,
      y,
    }));
    return { ok: true, forecast };
  } finally {
    conn.release();
  }
}

// ===== 3) Project delay risk (per project, heuristic) =====
export async function forecastDelayRisk({ project_id = null } = {}) {
  const conn = await mysqlPool.getConnection();
  try {
    const [rows] = await conn.query(`
      SELECT t.project_id, p.status, p.size, p.type,
             COALESCE(t.progress,0) AS progress,
             t.planned_end, t.actual_end,
             DATEDIFF(COALESCE(t.actual_end, CURDATE()), t.planned_end) AS delay_days
      FROM tasks t
      JOIN projects p ON p.id = t.project_id
    `);
    if (!rows.length) return { ok: false, error: "No task/project data" };

    const byProject = new Map();
    for (const r of rows) {
      const pid = Number(r.project_id);
      if (!byProject.has(pid)) byProject.set(pid, []);
      byProject.get(pid).push({
        progress: Number(r.progress || 0),
        delay_days: Number(r.delay_days || 0),
        size: String(r.size || ""),
        type: String(r.type || ""),
      });
    }

    const sizeW = (s) => ({ XL: 1.0, L: 0.7, M: 0.4, S: 0.2 }[s] || 0.3);
    const typeW = (t) => ({ cohort: 0.6, clinical: 0.5, survey: 0.3 }[t] || 0.3);

    const result = [];
    for (const [pid, arr] of byProject.entries()) {
      const prog = arr.reduce((s, a) => s + a.progress, 0) / arr.length;
      const delay = arr.reduce((s, a) => s + Math.max(0, a.delay_days), 0) / arr.length;
      const sW = sizeW((arr[0].size || "").toUpperCase());
      const tW = typeW((arr[0].type || "").toLowerCase());
      const score = 1.2 * (1 - prog) + 0.02 * delay + 0.5 * sW + 0.4 * tW;
      const prob = clamp(sigmoid(score - 1.2));
      result.push({ project_id: pid, risk: prob, progress: prog, delay_days: delay });
    }

    if (project_id !== null) {
      const one = result.find((r) => r.project_id === Number(project_id));
      return { ok: true, ...(one || { project_id, risk: 0 }) };
    }
    result.sort((a, b) => b.risk - a.risk);
    return { ok: true, projects: result };
  } finally {
    conn.release();
  }
}

// ===== 4) Output probability within this fiscal year (heuristic logistic) =====
export async function forecastOutputProb({ year = null } = {}) {
  const conn = await mysqlPool.getConnection();
  try {
    const [rows] = await conn.query(`
      SELECT p.id AS project_id,
             p.start_date, p.year, p.type, p.size,
             (SELECT COUNT(*) FROM outputs o WHERE o.project_id = p.id) AS out_count,
             (SELECT SUM(f.fte_value) FROM fte_roles f WHERE f.project_id = p.id) AS fte_sum,
             (SELECT MAX(irb.expiry_date) FROM irb WHERE irb.project_id = p.id) AS irb_expiry
      FROM projects p
    `);
    if (!rows.length) return { ok: false, error: "No project data" };

    const today = new Date();
    const feats = rows.map((r) => {
      const start = r.start_date ? new Date(r.start_date) : null;
      const ageDays = start ? Math.max(0, (today - start) / (1000 * 3600 * 24)) : 0;
      const irbValid = r.irb_expiry ? (new Date(r.irb_expiry) >= today ? 1 : 0) : 0;
      const sizeS = { XL: 3, L: 2, M: 1, S: 0 }[String(r.size || "").toUpperCase()] ?? 1;
      const typeS = { COHORT: 2, CLINICAL: 2, SURVEY: 1 }[String(r.type || "").toUpperCase()] ?? 1;
      const fte = Number(r.fte_sum || 0);
      const outCount = Number(r.out_count || 0);
      return { project_id: Number(r.project_id), ageDays, sizeS, typeS, fte, irbValid, outCount };
    });

    // ปรับน้ำหนักได้ตามข้อมูลจริง
    const W = { ageDays: 0.0015, sizeS: 0.25, typeS: 0.2, fte: 0.15, irbValid: 0.6, bias: -1.0 };
    const res = feats
      .map((f) => {
        const z =
          W.bias +
          W.ageDays * f.ageDays +
          W.sizeS * f.sizeS +
          W.typeS * f.typeS +
          W.fte * Math.min(2, f.fte) +
          W.irbValid * f.irbValid;
        const prob = clamp(sigmoid(z));
        return { project_id: f.project_id, prob, out_count: f.outCount };
      })
      .sort((a, b) => b.prob - a.prob);

    return { ok: true, probs: res };
  } finally {
    conn.release();
  }
}
