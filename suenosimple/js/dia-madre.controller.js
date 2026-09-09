import { addLead } from "./dia-madre.service.js";

// Todo: almacenar en CRM para obtener el ID 
const LANDING_ID = 5;

const feedbackMsg = document.getElementById("feedback");
const form = document.getElementById("form-leads");
form.addEventListener('submit', (e) => handleSubmit(e, new FormData(form)));

async function handleSubmit(event, formData) {
  event.preventDefault();

  try {
    const name = (formData.get("name") ?? "").trim();
    const phone = (formData.get("phone") ?? "").trim();
    const email = (formData.get("mail") ?? "").trim();
    
    validateLead({ name, phone, email });
    await addLead(LANDING_ID, { name, phone, mail });

    feedbackMsg.textContent =
      "✅ ¡Gracias! En breve recibirás toda la información.";

  } catch (error) {
    feedbackMsg.textContent = error.message
      ? `❌ ${error.message}`
      : "❌ Ocurrió un error almacenando tu información, intenta nuevamente en unos segundos.";
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
