window.searchItems = [
    {
        id: "foo",
        name: "finder",
        description: "hello world",
        related: "foobar",
        url: "gordon",
    },
    {
        id: "bar",
        name: "compiler",
        description: "test test",
        related: "baz qux",
        url: "freeman",
    },
    {
        id: "baz",
        name: "name",
        description: "hello world",
        related: "compiler compiler",
        url: "gordon",
    }
];

document.addEventListener(
    'DOMContentLoaded',
    function () {
        var searchNode = document.getElementById('search');
        Elm.Search.embed(searchNode, window.searchItems);
    }
);
