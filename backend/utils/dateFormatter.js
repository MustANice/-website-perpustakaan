const formatDate = (date) => {
  return new Date(date).toLocaleDateString("id-ID");
};

module.exports = formatDate;