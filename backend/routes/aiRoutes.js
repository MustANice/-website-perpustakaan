const express = require("express");
const router = express.Router();

const { askAI } = require("../controllers/aiController");

const protect = require("../middlewares/authMiddleware");

router.post("/", protect, askAI);

module.exports = router;