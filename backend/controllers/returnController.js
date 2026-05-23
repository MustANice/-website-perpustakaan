const Borrow = require("../models/Borrow");
const Return = require("../models/Return");
const Book = require("../models/Book");

const calculateFine = require("../services/fineCalculatorService");

const returnBook = async (req, res) => {
  const { borrowId } = req.body;

  const borrow = await Borrow.findById(borrowId);

  if (!borrow) {
    return res.status(404).json({
      message: "Borrow not found"
    });
  }

  const fine = calculateFine(
    new Date(borrow.dueDate),
    new Date()
  );

  const returned = await Return.create({
    borrow: borrowId,
    fine
  });

  borrow.status = "returned";

  await borrow.save();

  const book = await Book.findById(borrow.book);

  book.stock += 1;

  await book.save();

  res.json(returned);
};

module.exports = {
  returnBook
};