<?xml version="1.0" encoding="UTF-8"?>
<listing xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Ui:etc/ui_configuration.xsd">

    <container name="listing_top">
        <component name="columns_controls" class="Amasty\Pgrid\Ui\Component\Columns">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="component" xsi:type="string">Amasty_Pgrid/js/grid/controls/columns</item>
                    <item name="displayArea" xsi:type="string">dataGridActions</item>
                    <item name="columnsData" xsi:type="array">
                        <item name="provider" xsi:type="string">product_listing.product_listing.product_columns</item>
                    </item>
                    <item name="columnsExtra" xsi:type="array">
                        <item name="categories" xsi:type="string" translate="true">Categories</item>
                        <item name="links" xsi:type="string" translate="true">Links</item>
                    </item>
                    <item name="storageConfig" xsi:type="array">
                        <item name="provider" xsi:type="string">product_listing.product_listing.listing_top.bookmarks</item>
                        <item name="namespace" xsi:type="string">current</item>
                    </item>
                    <item name="editorCellConfig" xsi:type="array">
                        <item name="provider" xsi:type="string">product_listing.product_listing.product_columns_amasty_editor_cell</item>
                    </item>
                    <item name="listingFilterConfig" xsi:type="array">
                        <item name="provider" xsi:type="string">product_listing.product_listing.listing_top.listing_filters</item>
                    </item>
                    <item name="clientConfig" xsi:type="array">
                        <item name="saveUrl" xsi:type="url" path="amasty_pgrid/index/bookmarks"/>
                        <item name="validateBeforeSave" xsi:type="boolean">false</item>
                    </item>
                </item>
            </argument>
        </component>
    </container>

    <columns name="product_columns" class="Amasty\Pgrid\Ui\Component\Listing\Columns">
        <argument name="data" xsi:type="array">
            <item name="config" xsi:type="array">
                <item name="component" xsi:type="string">Amasty_Pgrid/js/grid/listing</item>
                <item name="amastyEditorConfig" xsi:type="array">
                    <item name="enabled" xsi:type="boolean">true</item>
                    <item name="clientConfig" xsi:type="array">
                        <item name="saveUrl" xsi:type="url" path="amasty_pgrid/index/inlineEdit"/>
                        <item name="validateBeforeSave" xsi:type="boolean">false</item>
                    </item>
                </item>
                <item name="childDefaults" xsi:type="array">
                    <item name="fieldAction" xsi:type="array">
                        <item name="provider" xsi:type="string">product_listing.product_listing.product_columns_amasty_editor</item>
                        <item name="target" xsi:type="string">startEdit</item>
                        <item name="params" xsi:type="array">
                            <item name="0" xsi:type="string">${ $.$data.rowIndex }</item>
                            <item name="1" xsi:type="string">${ $.$data.column.index }</item>
                        </item>
                    </item>

                </item>
            </item>
        </argument>
        <column name="name">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="editor" xsi:type="array">
                        <item name="editorType" xsi:type="string">text</item>
                        <item name="validation" xsi:type="string">required-entry</item>
                    </item>
                </item>
            </argument>
        </column>
        <column name="sku">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="editor" xsi:type="array">
                        <item name="editorType" xsi:type="string">text</item>
                        <item name="validation" xsi:type="string">required-entry</item>
                    </item>
                </item>
            </argument>
        </column>
        <column name="price">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="editor" xsi:type="array">
                        <item name="editorType" xsi:type="string">price</item>
                        <item name="validation" xsi:type="array">
                            <item name="required-entry" xsi:type="boolean">true</item>
                            <item name="validate-zero-or-greater" xsi:type="boolean">true</item>
                        </item>
                    </item>
                </item>
            </argument>
        </column>
        <column name="qty">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="editor" xsi:type="array">
                        <item name="editorType" xsi:type="string">text</item>
                        <item name="validation" xsi:type="array">
                            <item name="required-entry" xsi:type="boolean">true</item>
                            <!--<item name="validate-number" xsi:type="boolean">true</item>-->
                        </item>
                    </item>
                </item>
            </argument>
        </column>
        <column name="visibility">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="editor" xsi:type="string">select</item>
                </item>
            </argument>
        </column>
        <column name="status">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="editor" xsi:type="string">select</item>
                </item>
            </argument>
        </column>
        <column name="amasty_categories">
            <argument name="data" xsi:type="array">

                <item name="options" xsi:type="object">Amasty\Pgrid\Model\Config\Source\Categories</item>
                <item name="config" xsi:type="array">
                    <item name="filter" xsi:type="string">select</item>
                    <item name="dataType" xsi:type="string">select</item>

                    <item name="label" xsi:type="string" translate="true">Categories</item>
                    <item name="amastyExtra" xsi:type="boolean">true</item>
                    <item name="bodyTmpl" xsi:type="string">ui/grid/cells/html</item>
                    <item name="visible" xsi:type="boolean">false</item>
                </item>
            </argument>
        </column>
        <column name="amasty_link">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="label" xsi:type="string" translate="true">Front End Product Link</item>
                    <item name="amastyExtra" xsi:type="boolean">true</item>
                    <item name="fieldAction" xsi:type="boolean">false</item>
                    <item name="bodyTmpl" xsi:type="string">Amasty_Pgrid/ui/grid/cells/link</item>
                    <item name="visible" xsi:type="boolean">false</item>
                </item>
            </argument>
        </column>
        <column name="amasty_availability">
            <argument name="data" xsi:type="array">
                <item name="options" xsi:type="object">Amasty\Pgrid\Model\Product\Availability</item>
                <item name="config" xsi:type="array">
                    <item name="editor" xsi:type="string">select</item>
                    <item name="component" xsi:type="string">Magento_Ui/js/grid/columns/select</item>
                    <item name="dataType" xsi:type="string">select</item>
                    <item name="label" xsi:type="string" translate="true">Availability</item>
                    <item name="amastyExtra" xsi:type="boolean">true</item>
                    <item name="visible" xsi:type="boolean">false</item>
                </item>
            </argument>
        </column>
        <column name="amasty_created_at" class="Magento\Ui\Component\Listing\Columns\Date">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="component" xsi:type="string">Magento_Ui/js/grid/columns/date</item>
                    <item name="dataType" xsi:type="string">date</item>
                    <item name="label" xsi:type="string" translate="true">Creation Date</item>
                    <item name="amastyExtra" xsi:type="boolean">true</item>
                    <item name="visible" xsi:type="boolean">false</item>
                </item>
            </argument>
        </column>
        <column name="amasty_updated_at" class="Magento\Ui\Component\Listing\Columns\Date">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="component" xsi:type="string">Magento_Ui/js/grid/columns/date</item>
                    <item name="dataType" xsi:type="string">date</item>
                    <item name="label" xsi:type="string" translate="true">Last Modified Date</item>
                    <item name="amastyExtra" xsi:type="boolean">true</item>
                    <item name="visible" xsi:type="boolean">false</item>
                </item>
            </argument>
        </column>
        <column name="amasty_related_products">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="label" xsi:type="string" translate="true">Related Products</item>
                    <item name="amastyExtra" xsi:type="boolean">true</item>
                    <item name="bodyTmpl" xsi:type="string">ui/grid/cells/html</item>
                    <item name="visible" xsi:type="boolean">false</item>
                </item>
            </argument>
        </column>
        <column name="amasty_up_sells">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="label" xsi:type="string" translate="true">Up Sells</item>
                    <item name="amastyExtra" xsi:type="boolean">true</item>
                    <item name="bodyTmpl" xsi:type="string">ui/grid/cells/html</item>
                    <item name="visible" xsi:type="boolean">false</item>
                </item>
            </argument>
        </column>
        <column name="amasty_cross_sells">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="label" xsi:type="string" translate="true">Cross Sells</item>
                    <item name="amastyExtra" xsi:type="boolean">true</item>
                    <item name="bodyTmpl" xsi:type="string">ui/grid/cells/html</item>
                    <item name="visible" xsi:type="boolean">false</item>
                </item>
            </argument>
        </column>
        <column name="amasty_low_stock">
            <argument name="data" xsi:type="array">
                <item name="options" xsi:type="object">Amasty\Pgrid\Model\Product\Lowstock</item>
                <item name="config" xsi:type="array">
                    <item name="component" xsi:type="string">Magento_Ui/js/grid/columns/select</item>
                    <item name="dataType" xsi:type="string">select</item>
                    <item name="label" xsi:type="string" translate="true">Low Stock</item>
                    <item name="amastyExtra" xsi:type="boolean">true</item>
                    <item name="visible" xsi:type="boolean">false</item>
                </item>
            </argument>
        </column>

    </columns>
</listing>