const form = document.getElementById("form-leads");
form.addEventListener('submit', (e) => handleSubmit(e, new FormData(form)));

function handleSubmit(event, formData) {
  event.preventDefault();

  try {
    const name = formData.get("name");
    const phone = formData.get("phone");
    const email = formData.get("mail");

    console.log(name, phone, email);
  } catch (error) {
    console.error(error);
  }
}