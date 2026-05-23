const express = require("express");
const router = express.Router();

const { returnBook } = require("../controllers/returnController");

const protect = require("../middlewares/authMiddleware");

router.post("/", protect, returnBook);

module.exports = router;