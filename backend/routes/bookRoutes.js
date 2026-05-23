const express = require("express");
const router = express.Router();

const {
  createBook,
  getBooks,
  updateBook,
  deleteBook
} = require("../controllers/bookController");

const protect = require("../middlewares/authMiddleware");

const authorize = require("../middlewares/roleMiddleware");

router.post("/", protect, authorize("admin"), createBook);

router.get("/", getBooks);

router.put(
  "/:id",
  protect,
  authorize("admin"),
  updateBook
);

router.delete(
  "/:id",
  protect,
  authorize("admin"),
  deleteBook
);

module.exports = router;