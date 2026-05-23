const mongoose = require("mongoose");

const bookSchema = new mongoose.Schema(
  {
    title: String,

    author: String,

    category: String,

    stock: {
      type: Number,
      default: 0
    },

    publishedYear: Number
  },
  {
    timestamps: true
  }
);

module.exports = mongoose.model("Book", bookSchema);