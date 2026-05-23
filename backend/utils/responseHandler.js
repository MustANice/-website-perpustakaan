const successResponse = (res, message, data = null) => {
  res.status(200).json({
    success: true,
    message,
    data
  });
};

const errorResponse = (res, message) => {
  res.status(400).json({
    success: false,
    message
  });
};

module.exports = {
  successResponse,
  errorResponse
};