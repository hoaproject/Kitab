(function(){
    if (
        typeof self !== 'undefined' && !self.Prism ||
        typeof global !== 'undefined' && !global.Prism
    ) {
        return;
    }

    Prism.hooks.add('wrap', function(env) {
        if ('php' !== env.language ||
            'keyword' !== env.type) {
            return;
        }

        switch (env.content) {
            case 'public':
            case 'protected':
            case 'private':
                env.content =
                    '<abbr title="' + env.content + '">' +
                    env.content.substring(0, 3) +
                    '</abbr>';

                break;

            case 'function':
                env.content = '<abbr title="' + env.content + '">fn</abbr>';

                break;
            
        }
    });
})();
