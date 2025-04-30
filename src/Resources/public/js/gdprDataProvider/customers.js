/**
* This source file is available under the terms of the
* Pimcore Open Core License (POCL)
* Full copyright and license information is available in
* LICENSE.md which is distributed with this source code.
*
*  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.com)
*  @license    Pimcore Open Core License (POCL)
*/


pimcore.registerNS("pimcore.plugin.GDPRDataExtractorBundle.dataproviders.customers");
pimcore.plugin.GDPRDataExtractorBundle.dataproviders.customers = Class.create(pimcore.settings.gdpr.dataproviders.dataObjects, {

    title: t("cmf_gdpr_export_customers"),
    iconCls: "pimcore_icon_customers",
    searchUrl: Routing.generate('_pimcore_customermanagementframework_gdprdata_searchdataobjects'),
    downloadUrl: Routing.generate('_pimcore_customermanagementframework_gdprdata_export', {id: ''})

});
