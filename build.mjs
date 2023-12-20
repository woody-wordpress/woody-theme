import pipe from '../../../../builder/lib/pipe.mjs';
import javascripts from '../../../../builder/tasks/javascripts.mjs';

pipe({
    'dist': 'dist',
    'pipe': ['js'],
    'tasks': {
        'js': {
            'fn': javascripts,
            'config': {
                'source': ['Resources/Assets/js/*.+(js|mjs)'],
                'bundle': true,
                'revision': false,
                'format': 'esm',
                'minify': true,
                'target': 'js',
                'external': [
                    'jquery',
                ],
            },
        }
    }
});
