const User = require("../models/User");
const Book = require("../models/Book");
const Borrow = require("../models/Borrow");

const getDashboard = async (req, res) => {
  const totalUsers = await User.countDocuments();

  const totalBooks = await Book.countDocuments();

  const totalBorrows = await Borrow.countDocuments();

  res.json({
    totalUsers,
    totalBooks,
    totalBorrows
  });
};

module.exports = {
  getDashboard
};