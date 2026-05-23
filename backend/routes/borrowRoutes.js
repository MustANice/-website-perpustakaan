const express = require("express");
const router = express.Router();

const {
  borrowBook,
  getBorrows
} = require("../controllers/borrowController");

const protect = require("../middlewares/authMiddleware");

router.post("/", protect, borrowBook);

router.get("/", protect, getBorrows);

module.exports = router;