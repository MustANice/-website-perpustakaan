const nodemailer = require("nodemailer");
const { EMAIL_USER, EMAIL_PASS } = require("../config/env");

const transporter = nodemailer.createTransport({
  service: "gmail",
  auth: {
    user: EMAIL_USER,
    pass: EMAIL_PASS
  }
});

const sendNotification = async (to, subject, text) => {
  await transporter.sendMail({
    from: EMAIL_USER,
    to,
    subject,
    text
  });
};

module.exports = sendNotification;