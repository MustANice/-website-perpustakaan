const model = require("../config/gemini");

const getBookRecommendation = async (prompt) => {
  const result = await model.generateContent(prompt);

  return result.response.text();
};

module.exports = getBookRecommendation;