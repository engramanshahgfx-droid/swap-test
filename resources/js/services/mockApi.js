const delay = (ms = 350) => new Promise((resolve) => setTimeout(resolve, ms));

export async function fetchMock(payload) {
  await delay();
  return payload;
}

export function exportCsv(fileName, rows) {
  if (!rows?.length) return;
  const headers = Object.keys(rows[0]);
  const body = rows.map((r) => headers.map((h) => JSON.stringify(r[h] ?? '')).join(','));
  const csv = [headers.join(','), ...body].join('\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = fileName;
  link.click();
  URL.revokeObjectURL(link.href);
}