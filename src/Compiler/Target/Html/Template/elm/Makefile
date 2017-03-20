all:
	elm-make --output ../javascript/search.js Search.elm

compress:
	google-closure-compiler-js ../javascript/search.js > ../javascript/search.min.js
	mv ../javascript/search.min.js ../javascript/search.js
