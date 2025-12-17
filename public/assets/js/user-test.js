function show(msg) {
  const o = document.getElementById('output');
  o.classList.remove('d-none');
  o.innerText = msg;
}

function register() {
  fetch('user.php?action=register', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
      nome: r_nome.value,
      cognome: r_cognome.value,
      email: r_email.value,
      password: r_password.value
    })
  })
  .then(r => r.json())
  .then(d => show(d.message));
}

function login() {
  fetch('user.php?action=login', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
      email: l_email.value,
      password: l_password.value
    })
  })
  .then(r => r.json())
  .then(d => show(d.message));
}
