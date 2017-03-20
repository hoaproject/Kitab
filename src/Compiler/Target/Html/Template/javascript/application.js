document.addEventListener(
    'DOMContentLoaded',
    function () {
        var searchNode = document.getElementById('search');
        Elm.Search.embed(searchNode, window.searchItems);
    }
);
