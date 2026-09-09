const API_BASE_URL = 'http://localhost:3000/api/landings'

export async function addLead(id, { name, phone, email }) {
  const payload = { name, phone, email };
  const leadData = await fetch(`${API_BASE_URL}/${id}/leads`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(payload),
  })
  .then(response => {
    if (!response.ok)
      throw new Error(`Response status: ${response.status} ${response.statusText}`);

    return response.json();
  })
  .catch(error => {
    console.error(error);
    throw new Error();
  });

  return leadData;
}
