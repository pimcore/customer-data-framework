/**
* This source file is available under the terms of the
* Pimcore Open Core License (POCL)
* Full copyright and license information is available in
* LICENSE.md which is distributed with this source code.
*
*  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.com)
*  @license    Pimcore Open Core License (POCL)
*/


$(function(){

   var clipboard = new Clipboard('.js-copy-to-clipboard');

    clipboard.on('success', function(e) {
        alert('copied');
    });

});