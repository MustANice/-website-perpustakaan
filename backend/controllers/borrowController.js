const Borrow = require("../models/Borrow");
const Book = require("../models/Book");

const borrowBook = async (req, res) => {
  const { bookId, dueDate } = req.body;

  const book = await Book.findById(bookId);

  if (!book || book.stock <= 0) {
    return res.status(400).json({
      message: "Book unavailable"
    });
  }

  const borrow = await Borrow.create({
    user: req.user._id,
    book: bookId,
    dueDate
  });

  book.stock -= 1;

  await book.save();

  res.status(201).json(borrow);
};

const getBorrows = async (req, res) => {
  const borrows = await Borrow.find()
    .populate("user", "name")
    .populate("book", "title");

  res.json(borrows);
};

module.exports = {
  borrowBook,
  getBorrows
};