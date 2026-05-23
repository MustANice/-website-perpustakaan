const calculateFine = (dueDate, returnDate) => {
  const lateDays = Math.ceil(
    (returnDate - dueDate) / (1000 * 60 * 60 * 24)
  );

  if (lateDays > 0) {
    return lateDays * 2000;
  }

  return 0;
};

module.exports = calculateFine;