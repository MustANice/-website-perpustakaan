const jwt = require("jsonwebtoken");
const { JWT_SECRET } = require("../config/env");

const generateToken = (id, role) => {
  return jwt.sign(
    {
      id,
      role
    },
    JWT_SECRET,
    {
      expiresIn: "7d"
    }
  );
};

module.exports = generateToken;