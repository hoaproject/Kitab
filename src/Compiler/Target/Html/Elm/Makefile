all:
	elm-make --output ../Template/Public/javascript/search.elm.js Search.elm
	elm-make --output ../Javascript/search-index-builder.elm.js SearchIndexBuilder.elm

compress:
	google-closure-compiler-js ../Template/Public/javascript/search.elm.js > ../Template/Public/javascript/search.elm.min.js
	mv ../Template/Public/javascript/search.elm.min.js ../Template/Public/javascript/search.elm.js
	google-closure-compiler-js ../Javascript/search-index-builder.elm.js > ../Javascript/search-index-builder.min.js
	mv ../Javascript/search-index-builder.min.js ../Javascript/search-index-builder.elm.js
