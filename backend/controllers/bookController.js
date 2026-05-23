const Book = require("../models/Book");

const createBook = async (req, res) => {
  const book = await Book.create(req.body);

  res.status(201).json(book);
};

const getBooks = async (req, res) => {
  const books = await Book.find();

  res.json(books);
};

const updateBook = async (req, res) => {
  const book = await Book.findByIdAndUpdate(
    req.params.id,
    req.body,
    {
      new: true
    }
  );

  res.json(book);
};

const deleteBook = async (req, res) => {
  await Book.findByIdAndDelete(req.params.id);

  res.json({
    message: "Book deleted"
  });
};

module.exports = {
  createBook,
  getBooks,
  updateBook,
  deleteBook
};