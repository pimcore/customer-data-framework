# Customer Management Framework Pimcore plugin

### Documentation topics:

* [Activities (ActivityManager, ActivityStore, ActivityView)](./doc/Activities.md)
* [Customer Segments](./doc/CustomerSegments.md)
* [Customer Duplicates Service](./doc/CustomerDuplicatesService.md)
* [Customer Save Manager](./doc/CustomerSaveManager.md)
* [Webservice](./doc/Webservice.md)
* [Cronjobs](./doc/Cronjobs.md)
* [Single Sign On](./doc/Single_Sign_On.md)
* [Example configuration](./doc/ExampleConfiguraation.md)


## Development

### Frontend

Frontend assets for the admin iframes (`src/Resources/public/admin`) are built via gulp and generated from the `frontend/`
directory. If you need to change something frontend specific, please update the source files, run gulp and commit the 
generated files (for easier redistribution).

Assuming you have Node, NPM and Gulp installed, you can run the following from the root of the repository:

```bash
# install libraries
$ npm install

# run gulp
$ gulp

# or run gulp watch while developing
$ gulp watch

# or just build CSS (see gulp --tasks for a list of valid tasks)
$ gulp frontend:styles

# when you're done commit the generated files
$ git add src/Resources/public/admin && git commit
```

#### TODOs/nice to haves

* Port frontend build from gulp to webpack + make use of Symfony's webpack encore
