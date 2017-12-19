/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

pimcore.registerNS("pimcore.plugin.GDPRDataExtractorBundle.dataproviders.customers");
pimcore.plugin.GDPRDataExtractorBundle.dataproviders.customers = Class.create(pimcore.settings.gdpr.dataproviders.dataObjects, {

    title: t("cmf_gdpr_export_customers"),
    iconCls: "pimcore_icon_customers",
    searchUrl: "/admin/customermanagementframework/gdpr-data/search-data-objects",
    downloadUrl: "/admin/customermanagementframework/gdpr-data/export?id="

});
