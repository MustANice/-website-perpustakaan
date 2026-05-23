const mongoose = require("mongoose");

const borrowSchema = new mongoose.Schema(
  {
    user: {
      type: mongoose.Schema.Types.ObjectId,
      ref: "User"
    },

    book: {
      type: mongoose.Schema.Types.ObjectId,
      ref: "Book"
    },

    borrowDate: {
      type: Date,
      default: Date.now
    },

    dueDate: Date,

    status: {
      type: String,
      enum: ["borrowed", "returned"],
      default: "borrowed"
    }
  },
  {
    timestamps: true
  }
);

module.exports = mongoose.model("Borrow", borrowSchema);