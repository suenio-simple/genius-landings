const form = document.getElementById("form-leads");
form.addEventListener('submit', (e) => handleSubmit(e, new FormData(form)));

function handleSubmit(event, formData) {
  event.preventDefault();

  try {
    const name = (formData.get("name") ?? "").trim();
    const phone = (formData.get("phone") ?? "").trim();
    const email = (formData.get("mail") ?? "").trim();
    
    validateLead({ name, phone, email });

  } catch (error) {
    console.error(error);
  }
}

function validateLead({ name, phone, email }) {
  if (!name) {
    throw new Error("El nombre es obligatorio.");
  }

  if (!phone) {
    throw new Error("El teléfono es obligatorio.");
  }

  if (!email) {
    throw new Error("El email es obligatorio.");
  }

  if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email)) {
    throw new Error("El email debe tener un formato válido.");
  }

  if (!/^\d+$/.test(phone)) {
    throw new Error("El teléfono debe contener solo números.");
  }
}
