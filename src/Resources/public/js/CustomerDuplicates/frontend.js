/**
* This source file is available under the terms of the
* Pimcore Open Core License (POCL)
* Full copyright and license information is available in
* LICENSE.md which is distributed with this source code.
*
*  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.com)
*  @license    Pimcore Open Core License (POCL)
*/


$(function() {

    // Merge button in customer duplicates view
    const duplicates = document.getElementsByClassName('customer-duplicates-merge');
    for(let duplicate of duplicates){
        duplicate.addEventListener('click', (event) => {
            const duplicateIds = JSON.parse(duplicate.dataset.duplicateIds);
            new window.top.pimcore.plugin.objectmerger.panel(duplicateIds[0], duplicateIds[1]);
        });
    }
   
}());