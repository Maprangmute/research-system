// rag.js — MariaDB-only RAG (FULLTEXT/LIKE -> cosine in Node -> Gemini answer)
import mysql from "mysql2/promise";
import { GoogleGenerativeAI } from "@google/generative-ai";

// ===== DB Config (แก้ค่าที่นี่หรือใช้ Plesk Env Vars) =====
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

// ===== Gemini config =====
const GEMINI_API_KEY =
  process.env.GEMINI_API_KEY || "PUT_YOUR_GEMINI_API_KEY_HERE";
const genAI = new GoogleGenerativeAI(GEMINI_API_KEY);
const EMBED_MODEL = "text-embedding-004"; // 768-dims

async function embed(texts) {
  const model = genAI.getGenerativeModel({ model: EMBED_MODEL });
  const arr = Array.isArray(texts) ? texts : [texts];
  const res = await model.embedContent({
    contents: arr.map((t) => ({ role: "user", parts: [{ text: String(t) }] })),
  });
  const vectors = res.embeddings?.map((e) => e.values) || [];
  return Array.isArray(texts) ? vectors : vectors[0] || [];
}

// ===== cosine helpers =====
const dot = (a, b) => a.reduce((s, v, i) => s + v * (b[i] || 0), 0);
const norm = (a) => Math.sqrt(a.reduce((s, v) => s + v * v, 0)) || 1e-9;
const cosine = (a, b) => dot(a, b) / (norm(a) * norm(b));

// ===== สร้างตาราง docs ถ้ายังไม่มี =====
async function ensureDocsTable() {
  const conn = await mysqlPool.getConnection();
  try {
    await conn.query(`
      CREATE TABLE IF NOT EXISTS docs (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        source_table VARCHAR(64) NOT NULL,
        source_id VARCHAR(64) NOT NULL,
        title TEXT,
        text LONGTEXT NOT NULL,
        embedding_json LONGTEXT NULL,
        meta JSON NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_source (source_table, source_id)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    `);
    // สร้าง FULLTEXT index ถ้าทำได้
    try {
      await conn.query(
        `ALTER TABLE docs ADD FULLTEXT KEY ft_docs (title, text);`
      );
    } catch (_e) {
      // อาจมีอยู่แล้ว หรือ engine ไม่รองรับ — ข้ามไป
    }
  } finally {
    conn.release();
  }
}

// ===== ดึงข้อมูลจากตารางจริง -> แปลงเป็นชิ้นความรู้ =====
async function fetchRows(table, limit) {
  const conn = await mysqlPool.getConnection();
  try {
    if (table === "projects") {
      const [rows] = await conn.query(
        `SELECT id, name AS title,
                CONCAT_WS('\n', name, description, status, type, size, year, start_date, end_date) AS text
         FROM projects ORDER BY id DESC LIMIT ?`,
        [limit]
      );
      return rows.map((r) => ({
        source_table: "projects",
        source_id: String(r.id),
        title: r.title,
        text: r.text,
      }));
    }
    if (table === "fte_roles") {
      const [rows] = await conn.query(
        `SELECT id, CONCAT_WS(' ', prefix, name) AS title,
                CONCAT_WS('\n', role_group, job_detail, project_id, person_id, start_date, end_date, fte_value) AS text
         FROM fte_roles ORDER BY id DESC LIMIT ?`,
        [limit]
      );
      return rows.map((r) => ({
        source_table: "fte_roles",
        source_id: String(r.id),
        title: r.title,
        text: r.text,
      }));
    }
    if (table === "outputs") {
      const [rows] = await conn.query(
        `SELECT id, title,
                CONCAT_WS('\n', type, export_year, project_id, authors) AS text
         FROM outputs ORDER BY id DESC LIMIT ?`,
        [limit]
      );
      return rows.map((r) => ({
        source_table: "outputs",
        source_id: String(r.id),
        title: r.title,
        text: r.text,
      }));
    }
    if (table === "irb") {
      const [rows] = await conn.query(
        `SELECT id, project_id AS title,
                CONCAT_WS('\n', irb_code, issue_date, expiry_date, status) AS text
         FROM irb ORDER BY id DESC LIMIT ?`,
        [limit]
      );
      return rows.map((r) => ({
        source_table: "irb",
        source_id: String(r.id),
        title: String(r.title),
        text: r.text,
      }));
    }
    return [];
  } finally {
    conn.release();
  }
}

// ===== Upsert ลง docs พร้อม embedding_json =====
async function upsertDocs(docs) {
  if (!docs.length) return { inserted: 0 };
  const texts = docs.map((d) => d.text);
  const vectors = await embed(texts);

  const conn = await mysqlPool.getConnection();
  try {
    const sql = `INSERT INTO docs (source_table, source_id, title, text, embedding_json)
                 VALUES (?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                   title=VALUES(title),
                   text=VALUES(text),
                   embedding_json=VALUES(embedding_json),
                   updated_at=CURRENT_TIMESTAMP`;
    let inserted = 0;
    for (let i = 0; i < docs.length; i++) {
      const d = docs[i];
      const vec = JSON.stringify(vectors[i]);
      await conn.query(sql, [
        d.source_table,
        d.source_id,
        d.title || null,
        d.text,
        vec,
      ]);
      inserted++;
    }
    return { inserted };
  } finally {
    conn.release();
  }
}

export async function reindexDocs(tables = ["projects"], limit = 1000) {
  await ensureDocsTable();
  const report = {};
  for (const t of tables) {
    const rows = await fetchRows(t, limit);
    const res = await upsertDocs(rows);
    report[t] = res.inserted;
  }
  return report;
}

// ===== ดึงแคนดิเดตด้วย FULLTEXT/LIKE -> จัดอันดับด้วย cosine =====
async function candidateDocs(query, topN = 800) {
  const conn = await mysqlPool.getConnection();
  try {
    // FULLTEXT ก่อน ถ้าล้มเหลวค่อย LIKE
    try {
      const [rows] = await conn.query(
        `SELECT id, source_table, source_id, title, text, embedding_json
           FROM docs
          WHERE MATCH(title, text) AGAINST (? IN NATURAL LANGUAGE MODE)
          LIMIT ?`,
        [query, topN]
      );
      if (rows.length) return rows;
    } catch (_e) {}
    const likeQ = `%${String(query).slice(0, 128)}%`;
    const [rows] = await conn.query(
      `SELECT id, source_table, source_id, title, text, embedding_json
         FROM docs
        WHERE title LIKE ? OR text LIKE ?
        LIMIT ?`,
      [likeQ, likeQ, topN]
    );
    return rows;
  } finally {
    conn.release();
  }
}

export async function askRAG(query, { topK = 5 } = {}) {
  await ensureDocsTable();
  const qVec = await embed(query);
  const rows = await candidateDocs(query, 800);

  const scored = rows
    .map((r) => {
      const vec = r.embedding_json ? JSON.parse(r.embedding_json) : [];
      const score = vec.length === qVec.length ? cosine(qVec, vec) : 0;
      return { ...r, score };
    })
    .sort((a, b) => b.score - a.score)
    .slice(0, topK);

  const contextText = scored
    .map(
      (r, i) => `[#${i + 1}] ${r.title || r.source_table} (src=${r.source_table}:${r.source_id})
${r.text}`
    )
    .join("\n\n");

  const sys =
    "You are a bilingual (Thai/English) analyst for SIHP research database. Answer clearly with bullets or a short paragraph. Always cite sources as [#n] from the provided CONTEXT. If data is insufficient, say so and propose next steps.";
  const model = genAI.getGenerativeModel({ model: "gemini-2.5-flash" });
  const result = await model.generateContent(`SYSTEM
${sys}

QUESTION
${query}

CONTEXT
${contextText}`);
  const answer = result.response?.text?.() ?? "";

  const citations = scored.map((r, i) => ({
    id: r.id,
    n: i + 1,
    source: `${r.source_table}:${r.source_id}`,
    title: r.title,
    score: Number(r.score.toFixed(4)),
  }));

  return { answer, citations };
}
