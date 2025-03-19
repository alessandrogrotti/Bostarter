// Funzione per aggiungere un utente
function addUser(name) {
    fetch('http://localhost/backend/add_user.php', {  // Usa il nome corretto del servizio Docker
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'  // Utilizziamo JSON per inviare i dati
        },
        body: JSON.stringify({ name: name })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('User added successfully');
            getUsers();  // Aggiorna la lista degli utenti
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error adding user:', error);
        alert('An error occurred while adding the user');
    });
}

// Funzione per rimuovere un utente
function removeUser(id) {
    fetch('http://localhost/backend/remove_user.php', {  // Usa il nome corretto del servizio Docker
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'  // Utilizziamo JSON per inviare i dati
        },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('User removed successfully');
            getUsers();  // Aggiorna la lista degli utenti
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error removing user:', error);
        alert('An error occurred while removing the user');
    });
}

// Funzione per ottenere la lista degli utenti
function getUsers() {
    fetch('http://localhost/backend/test.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Errore nella risposta del server: ' + response.statusText);
            }
            return response.json();  // Converti solo se la risposta è valida
        })
        .then(data => {
            // Aggiungi i dati in una lista nel frontend
            let usersList = document.getElementById('usersList');
            usersList.innerHTML = '';  // Pulisce la lista precedente
            if (data.length === 0) {
                usersList.innerHTML = '<li>No users found</li>';
            }
            data.forEach((user, index) => {
                let listItem = document.createElement('li');
                
                // Mostra solo il numero dell'utente incrementato (partendo da 1)
                listItem.textContent = `User #${index + 1}: ${user.name}`;

                // Crea il pulsante "Rimuovi"
                let removeButton = document.createElement('button');
                removeButton.textContent = 'Rimuovi';
                removeButton.onclick = function() {
                    removeUser(user.id);
                };

                // Aggiungi il pulsante al listItem
                listItem.appendChild(removeButton);
                usersList.appendChild(listItem);
            });
        })
        .catch(error => {
            console.error('Errore nel recupero degli utenti:', error);
            let usersList = document.getElementById('usersList');
            usersList.innerHTML = '<li>Error retrieving users</li>';
        });
}

// Esegui la funzione per caricare gli utenti quando la pagina si carica
document.addEventListener('DOMContentLoaded', function() {
    getUsers();  // Carica tutti gli utenti all'avvio
});

// Aggiungi un nuovo utente quando il form viene inviato
document.getElementById('data-form').addEventListener('submit', function(event) {
    event.preventDefault();  // Previeni il comportamento di invio del form
    const name = document.getElementById('name').value;
    if (name.trim() === '') {
        alert('Name cannot be empty');
        return;
    }
    addUser(name);  // Aggiungi l'utente
});
