const mongoose = require("mongoose");

const returnSchema = new mongoose.Schema(
  {
    borrow: {
      type: mongoose.Schema.Types.ObjectId,
      ref: "Borrow"
    },

    returnDate: {
      type: Date,
      default: Date.now
    },

    fine: {
      type: Number,
      default: 0
    }
  },
  {
    timestamps: true
  }
);

module.exports = mongoose.model("Return", returnSchema);