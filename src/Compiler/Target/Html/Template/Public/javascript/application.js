document.addEventListener(
    'DOMContentLoaded',
    function () {
        var searchNode = document.querySelector('#search > div');
        Elm.Search.init(
            {
                node: searchNode,
                flags: {
                    serializedSearchIndex: window.searchIndex || "",
                    searchDatabase: window.searchMetadata || []
                }
            }
        );
    }
);
