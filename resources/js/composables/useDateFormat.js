export function useDateFormat() {
  const format = (date, options = {}) =>
    new Date(date).toLocaleString(undefined, options)

  return { format }
}