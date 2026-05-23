const getBookRecommendation = require("../services/aiRecommendationService");

const askAI = async (req, res) => {
  try {
    const { prompt } = req.body;

    const response = await getBookRecommendation(prompt);

    res.json({
      result: response
    });
  } catch (error) {
    res.status(500).json({
      message: error.message
    });
  }
};

module.exports = {
  askAI
};