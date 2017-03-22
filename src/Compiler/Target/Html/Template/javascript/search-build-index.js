var process = require('process');
var fs      = require('fs');
var Elm     = require('./search-index-builder.elm');

var options = process.argv.slice(2);

if (0 === options.length) {
    console.error('One argument is missing: Search database file.');
    process.exit(1);
}

var searchDatabase = options[0];

if (false === fs.existsSync(searchDatabase)) {
    console.error('Provided search database file does not exist: `' + searchDatabase + '`.');
    process.exit(2);
}

try {
    var searchDatabaseDecoded = JSON.parse(fs.readFileSync(searchDatabase, 'utf8'));
} catch (e) {
    console.log('Search database does not contain valid JSON data. The JSON parser error bellow:' + "\n");
    console.log(e);
    process.exit(3);
}

var app = Elm.SearchIndexBuilder.worker();

app.ports.output.subscribe(list => console.log(list));
app.ports.input.send(searchDatabaseDecoded);
