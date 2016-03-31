/* global module:false */
module.exports = function(grunt) {
   grunt.initConfig({
      app: grunt.file.readJSON('package.json'),
      sass: {
         dist: {
            options: {
               style: 'expanded'
            },
            files: {
               'Resources/Public/Styles/backend.css': 'Resources/Private/Styles/backend.scss',
               'Resources/Public/Styles/frontend.css': 'Resources/Private/Styles/frontend.scss',
               'Resources/Public/Styles/Error.css': 'Resources/Private/Styles/Error.scss'
            }
         }
      },
      watch: {
         css: {
            files: ['Resources/Private/Styles/*.scss'],
            tasks: ['sass', 'autoprefixer']
         },
         js: {
            files: ['Resources/Private/Scripts/*.js'],
            tasks: ['concat:core', 'uglify:core']
         }
      },
      clean: ['Resources/Public/Vendor/'],
      copy: {
         vendor: {
            files: [{
               expand: true,
               cwd: 'Resources/Private/Vendor/bootstrap/dist',
               src: ['**'],
               dest: 'Resources/Public/Vendor/bootstrap'
            }, {
               expand: true,
               cwd: 'Resources/Private/Vendor/Clockpicker/dist',
               src: ['bootstrap-clockpicker.css', 'bootstrap-clockpicker.js'],
               dest: 'Resources/Public/Vendor/Clockpicker'
            }, {
               expand: true,
               cwd: 'Resources/Private/Vendor/CSS3-animate-it',
               src: ['css/*.css', 'js/*.js'],
               dest: 'Resources/Public/Vendor/CSS3-animate-it'
            }, {
               expand: true,
               cwd: 'Resources/Private/Vendor/Datepicker/dist',
               src: ['**'],
               dest: 'Resources/Public/Vendor/Datepicker'
            }, {
               expand: true,
               cwd: 'Resources/Private/Vendor/Font-Awesome',
               src: ['css/*.css', 'fonts/*'],
               dest: 'Resources/Public/Vendor/Font-Awesome'
            }, {
               expand: true,
               cwd: 'Resources/Private/Vendor/Magnific-Popup/dist',
               src: ['*.css', '*.js'],
               dest: 'Resources/Public/Vendor/Magnific-Popup'
            }, {
               expand: true,
               cwd: 'Resources/Private/Vendor/socialshareprivacy',
               src: ['lang/*', 'images/*', 'socialshareprivacy.css', 'jquery.socialshareprivacy.js'],
               dest: 'Resources/Public/Vendor/socialshareprivacy'
            }, {
               expand: true,
               cwd: 'Resources/Private/Vendor/rangeslider.js/dist',
               src: ['*'],
               dest: 'Resources/Public/Vendor/rangeslider.js'
            }]
         }
      },
      concat: {
         vendor: {
            src: ['Resources/Public/Vendor/bootstrap/js/bootstrap.js', 'Resources/Public/Vendor/Clockpicker/bootstrap-clockpicker.js', 'Resources/Public/Vendor/CSS3-animate-it/js/css3-animate-it.js', 'Resources/Public/Vendor/Datepicker/js/bootstrap-datepicker.js', 'Resources/Public/Vendor/Magnific-Popup/jquery.magnific-popup.js', 'Resources/Public/Vendor/socialshareprivacy/jquery.socialshareprivacy.js', 'Resources/Public/Vendor/rangeslider.js/rangeslider.js'],
            dest: 'Resources/Public/Vendor/_vendor.js'
         },
         core: {
            src: ['Resources/Private/Scripts/main.config.js', 'Resources/Private/Scripts/main.*.js', 'Resources/Private/Scripts/main.js'],
            dest: 'Resources/Public/Scripts/main.js'
         }
      },
      uglify: {
         vendor: {
            options: {
               mangle: false,
               sourceMap: true,
               preserveComments: 'some'
            },
            files: {
               'Resources/Public/Vendor/_vendor.min.js': ['Resources/Public/Vendor/_vendor.js']
            }
         },
         core: {
            options: {
               mangle: false,
               sourceMap: true,
               preserveComments: 'some'
            },
            files: {
               'Resources/Public/Scripts/main.min.js': ['Resources/Public/Scripts/main.js']
            }
         }
      },
      cssmin: {
         options: {
            relativeTo: 'Resources/Public/Vendor/',
            target: 'Resources/Public/Vendor/_vendor.min.css'
         },
         target: {
            files: {
               'Resources/Public/Vendor/_vendor.min.css': ['Resources/Public/Vendor/_vendor.css']
            }
         }
      },
      css_url_relative: {
         styles: {
            options: {
               staticRoot: 'Resources/Public/Vendor/'
            },
            files: [{
               src: ['Resources/Public/Vendor/bootstrap/css/bootstrap.css', 'Resources/Public/Vendor/socialshareprivacy/socialshareprivacy.css', 'Resources/Public/Vendor/Clockpicker/bootstrap-clockpicker.css', 'Resources/Public/Vendor/CSS3-animate-it/css/animations.css', 'Resources/Public/Vendor/Datepicker/css/bootstrap-datepicker.css', 'Resources/Public/Vendor/Font-Awesome/css/font-awesome.css', 'Resources/Public/Vendor/Magnific-Popup/magnific-popup.css', 'Resources/Public/Vendor/rangeslider.js/rangeslider.css'],
               dest: 'Resources/Public/Vendor/_vendor.css'
            }]
         }
      },
      json: {
         config: {
            options: {
               namespace: 'drk'
            },
            src: ['config.json'],
            dest: 'Resources/Private/Scripts/main.config.js'
         }
      },
      jsbeautifier: {
         files: ['Gruntfile.js', 'Resources/Private/Scripts/*.js'],
         options: {
            config: '.jsbeautifyrc'
         }
      },
      autoprefixer: {
         no_dest: {
            src: ['Resources/Public/Styles/frontend.css', 'Resources/Public/Styles/backend.css']
         }
      }
   });

   grunt.loadNpmTasks('grunt-contrib-clean');
   grunt.loadNpmTasks('grunt-contrib-sass');
   grunt.loadNpmTasks('grunt-contrib-watch');
   grunt.loadNpmTasks('grunt-contrib-concat');
   grunt.loadNpmTasks('grunt-contrib-copy');
   grunt.loadNpmTasks('grunt-contrib-cssmin');
   grunt.loadNpmTasks('grunt-contrib-uglify');
   grunt.loadNpmTasks('grunt-css-url-relative');
   grunt.loadNpmTasks('grunt-json');
   grunt.loadNpmTasks('grunt-jsbeautifier');
   grunt.loadNpmTasks('grunt-autoprefixer');

   grunt.registerTask('default', ['sass', 'autoprefixer', 'json:config', 'concat:core', 'uglify:core', 'watch']);

   grunt.registerTask('commit', ['jsbeautifier']);

   grunt.registerTask('build', ['clean', 'json', 'copy', 'css_url_relative', 'cssmin', 'concat', 'uglify']);
};
