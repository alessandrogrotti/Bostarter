// Connessione al server MongoDB
use Bostarter;

// Crea la collezione per i log (se non esiste già)
db.createCollection("logs");

// Aggiunge un indice sul timestamp per ottimizzare eventuali ricerche
db.logs.createIndex({ timestamp: 1 });

// Inserisce un log di esempio per testare
db.logs.insertOne({
    action: "test_log",
    timestamp: new Date(),
    details: "This is a test log entry"
});

db.logs.deleteMany({});

