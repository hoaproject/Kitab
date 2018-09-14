const process = require('process');
const fs      = require('fs');
const { Elm } = require('./search-index-builder.elm');

var options = process.argv.slice(2);

if (0 === options.length) {
    console.error('One argument is missing: Search database file.');
    process.exit(1);
}

if (1 === options.length) {
    console.error('One argument is missing: Output file.');
    process.exit(1);
}

var searchDatabase = options[0];
var output         = options[1];

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

var app = Elm.SearchIndexBuilder.init();

app.ports.output.subscribe(list => {
    fs.writeFileSync(
        output,
        'window.searchIndex = \'' + list.replace(/\\\\/g, '\\\\\\\\').replace(/'/g, "\\'") + '\''
    );
});
app.ports.input.send(searchDatabaseDecoded);
