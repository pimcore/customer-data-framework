# Contributing to Customer Management Framework Bundle of Pimcore

## Bug Reports & Feature Requests
The Pimcore team heavily uses (and loves!) GitHub for all of our software management. 
We use GitHub issues exclusively to track all bugs and features.

* [Open an issue](https://github.com/pimcore/customer-data-framework/issues) here on GitHub. 
If you can, **please provide a fix and create a pull request (PR) instead**; this will automatically create an issue for you.
* Report security issues only to security@pimcore.org 
* Please be patient as not all items will be tested immediately - remember, Pimcore is open source and free of charge. 
* Occasionally we'll close issues if they appear stale or are too vague - please don't take this personally! 
Please feel free to re-open issues we've closed if there's something we've missed and they still need to be addressed.


## Development 

### Migrations
Database and other updates outside of the file system or the vendor folders need to be handled by Pimcore's bundle migrations feature.

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
