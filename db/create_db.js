// Connessione al server MongoDB
use Bostarter;

// Elimina la collezione 'logs' se esiste già
if (db.logs) {
    db.logs.drop();
}

// Crea la collezione per i log
db.createCollection("logs");

// Aggiunge un indice sul timestamp per ottimizzare eventuali ricerche
db.logs.createIndex({ timestamp: 1 });

