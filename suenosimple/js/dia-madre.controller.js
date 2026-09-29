import { addLead } from "./dia-madre.service.js";

// Todo: almacenar en CRM para obtener el ID 
let LANDING_ID = null;

async function resolveLandingId() {
  const res = await fetch('http://localhost:3000/api/landings');
  if (!res.ok) throw new Error('No se pudo resolver la landing');
  const landings = await res.json();
  const landing = landings.find(l =>
    l.name === 'Día de la Madre' && l.client === 'SuenoSimple'
  );
  if (!landing) throw new Error('Landing "Día de la Madre" no encontrada');
  return landing.id;
}

resolveLandingId()
  .then(id => { LANDING_ID = id; })
  .catch(err => addFeedbackMessage(`❌ ${err.message}`, "error"));

const feedbackMsg = document.getElementById("feedback");
const form = document.getElementById("form-leads");
form.addEventListener('submit', (e) => handleSubmit(e, new FormData(form)));

async function handleSubmit(event, formData) {
  event.preventDefault();

  if (!LANDING_ID) {
    addFeedbackMessage("❌ La landing todavía no cargó, esperá un segundo e intentá de nuevo.", "error");
    return;
  }

  try {
    const name = (formData.get("name") ?? "").trim();
    const phone = (formData.get("phone") ?? "").trim();
    const email = (formData.get("mail") ?? "").trim();
    const message = (formData.get("message") ?? "").trim();

    validateLead({ name, phone, email });
    await addLead(LANDING_ID, { name, phone, email, message });

    addFeedbackMessage("✅ ¡Gracias! En breve recibirás toda la información.");
    form.reset();

  } catch (error) {
    addFeedbackMessage(
      error.message
        ? `❌ ${error.message}`
        : "❌ Ocurrió un error almacenando tu información, intenta nuevamente en unos segundos.",
      "error"
    );
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

/**
 * Agrega un mensaje de feedback temporal al formulario de captación de leads
 * 
 * @param {string} message            - Mensaje a mostrar
 * @param {"success" | "error"} type  - Tipo de mensaje, "success" para mensajes de éxito y "error" para errores
 * @param {number} time               - Tiempo a mostrar el mensaje en milisegundos, por defecto 3000ms
 */
function addFeedbackMessage(message, type = "success", time = 3000) {
  feedbackMsg.textContent = message;
  feedbackMsg.classList.add("show");

  if (type === "error")
    feedbackMsg.classList.add("error");

  setTimeout(() => {
    feedbackMsg.textContent = "";
    feedbackMsg.className = "feedback";
  }, time);
}