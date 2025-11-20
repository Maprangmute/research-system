// app.js (เพิ่ม endpoint live)
import express from "express";
import cors from "cors";
import { GoogleGenerativeAI } from "@google/generative-ai";

const app = express();
app.use(cors());
app.use(express.json());

const PORT = process.env.PORT || 3000;
const HOST = process.env.HOST || "127.0.0.1";
const GEMINI_API_KEY = process.env.GEMINI_API_KEY;

if (!GEMINI_API_KEY) console.warn("⚠️ Missing GEMINI_API_KEY env var");

const genAI = new GoogleGenerativeAI(GEMINI_API_KEY);

// health check
app.get("/", (_req, res) => res.send("Node.js Gemini Service is running."));
app.get("/healthz", (_req, res) => res.send("OK"));

// 1️⃣ endpoint ปกติ (flash)
app.post("/api/ask-gemini", async (req, res) => {
  try {
    const prompt = (req.body?.prompt || "").toString().trim();
    if (!prompt) return res.status(400).json({ ok: false, error: "Prompt is required." });

    const model = genAI.getGenerativeModel({ model: "gemini-2.5-flash" });
    const result = await model.generateContent(prompt);
    const text = result.response?.text?.() ?? "";

    res.json({ ok: true, text });
  } catch (e) {
    console.error("Gemini Error:", e?.response?.data ?? e);
    res.status(500).json({ ok: false, error: "Failed to call Gemini API." });
  }
});

// 2️⃣ endpoint live (streaming)
app.post("/api/ask-gemini-live", async (req, res) => {
  res.setHeader("Content-Type", "text/event-stream");
  res.setHeader("Cache-Control", "no-cache");
  res.setHeader("Connection", "keep-alive");

  try {
    const { prompt } = req.body;
    if (!prompt) {
      res.write("data: Error: prompt required\n\n");
      return res.end();
    }

    const model = genAI.getGenerativeModel({ model: "gemini-2.5-flash-live" });
    const stream = await model.generateContentStream(prompt);

    // ส่งข้อความออกเป็น event stream ทีละ chunk
    for await (const chunk of stream.stream) {
      const chunkText = chunk.text();
      res.write(`data: ${chunkText}\n\n`);
    }

    res.write("data: [END]\n\n");
    res.end();
  } catch (err) {
    console.error("Gemini Live Error:", err);
    res.write(`data: Error: ${err.message}\n\n`);
    res.end();
  }
});

app.listen(PORT, HOST, () => {
  console.log(`✅ Node app listening on http://${HOST}:${PORT}`);
});

