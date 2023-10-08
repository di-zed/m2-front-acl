# DiZed Magento 2 Front ACL Module

## ACL System for the Front Part of the Magento 2 project.

A module that will allow you to take full advantage of the front-end ACL capabilities in the modules you develop.

###### Developed and tested on Magento 2.4 version and PHP 7.4 version.

##### Key Features:

- Ability to create your own roles and permissions via XML file.
- Ability to set default permissions for each of the roles.
- Ability to set the necessary roles and permissions for each customer in the admin panel.
- Ability to automatically set the default role and permissions for a newly created customer (using a plugin).
- Ability to easily use this functionality for controllers/actions and blocks.

### Installation.

```code
composer require dized/module-front-acl

bin/magento setup:upgrade --keep-generated
bin/magento setup:di:compile
bin/magento cache:clean
```

**IMPORTANT** to enable the module in Magento Admin: **Admin Panel -> Stores -> Settings -> Configuration -> DiZed Team Extensions -> Front ACL**.

![Module Configuration](https://raw.githubusercontent.com/di-zed/internal-storage/main/readme/images/m2-front-acl/config_front_acl.png)

### Adding Roles and Permissions.

To add new roles or permissions, create the **etc/acl_front.xml** file in your own project module. You can see an example of a file in this module.

Roles are inserted into the **FrontAcl_Role::index** resource, permissions are inserted into the **FrontAcl_Permission::index** resource.

If you want to set the default permissions for some roles, use the **FrontAcl_Defaults::index** resource. Pay attention to the syntax - *Relation_ {RoleCamelCase}::permission_snake_case*.

```xml
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Acl/etc/acl.xsd">
    <acl>
        <resources>
            <!-- Roles: -->
            <resource id="FrontAcl_Role::index" title="Roles" translate="title">
                <resource id="FrontAcl_Role::buyer" title="Buyer" translate="title"/>
                <resource id="FrontAcl_Role::seller" title="Seller" translate="title"/>
            </resource>
            <!-- Permissions: -->
            <resource id="FrontAcl_Permission::index" title="Permissions" translate="title">
                <resource id="FrontAcl_Permission::catalog" title="Catalog" translate="title"/>
                <resource id="FrontAcl_Permission::checkout" title="Checkout" translate="title"/>
                <resource id="FrontAcl_Permission::wishlist" title="Wishlist" translate="title"/>
            </resource>
            <!-- Defaults (default relations between roles and permissions): -->
            <resource id="FrontAcl_Defaults::index" title="Default Relations" translate="title">
                <resource id="Relation_Buyer::catalog" title="Catalog"/>
                <resource id="Relation_Buyer::checkout" title="Checkout"/>
                <resource id="Relation_Buyer::wishlist" title="Wishlist"/>
                <resource id="Relation_Seller::catalog" title="Catalog"/>
            </resource>
        </resources>
    </acl>
</config>
```

### Set customer roles and permissions in admin panel.

To set a role or permissions for a customer through the admin panel, just log in as an administrator and follow the following path: **Customers -> All Customers -> Click to "Edit" for a customer in the grid -> Tab Front ACL**. Select the required data here and save the customer.

![Customer Settings](https://raw.githubusercontent.com/di-zed/internal-storage/main/readme/images/m2-front-acl/customer_front_acl.png)

**IMPORTANT** If the permissions tree is not displayed, it is very likely that you are using a version of Magento with an older version of the jQuery jsTree library.
In this case, try using the [previous script "view/adminhtml/web/js/customer/permissions-tree.js" version](https://raw.githubusercontent.com/di-zed/m2-front-acl/21ceaa62f3a93cacce0a76ef2ef33ebf9a773430/view/adminhtml/web/js/customer/permissions-tree.js).

### Set the default role and permissions for a newly created customer automatically.

If you need to set roles and permissions for a new customer automatically, you can create a plugin for the function: *\DiZed\FrontAcl\Plugin\Customer\Api\AccountManagement::getDetectedRole(CustomerInterface $customer)*. In the body of the function, you need to implement the necessary logic based on the customer object and return the required role as a string. Further, based on this role, default permissions will be automatically taken and applied to the customer.

```php
    /**
     * Add custom logic to identify customer role.
     *
     * @param \DiZed\FrontAcl\Plugin\Customer\Api\AccountManagement $subject
     * @param string $result
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return string
     * @see \DiZed\FrontAcl\Plugin\Customer\Api\AccountManagement::getDetectedRole
     */
    public function afterGetDetectedRole(
        \DiZed\FrontAcl\Plugin\Customer\Api\AccountManagement $subject,
        string $result,
        CustomerInterface $customer
    ): string {
        // @todo Need to add custom logic here...
        return $result;
    }
```

### Use this functionality for controllers/actions and blocks.

To check the role or permissions of a customer in a controller/action you need to implement the **\DiZed\FrontAcl\Controller\App\HttpAclActionInterface** interface and use the **FRONT_ACL_ROLE** or **FRONT_ACL_PERMISSION** constants. You can also use the **isFrontClassAllowed()** public function to specify more precise conditions.

```php
<?php
namespace Name\Space\Sample\Action;

use DiZed\FrontAcl\Controller\App\HttpAclActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Sample implements HttpGetActionInterface, HttpAclActionInterface
{
    /**
     * Front ACL Role.
     */
    const FRONT_ACL_ROLE = ['FrontAcl_Role::sample'];
    
    /**
     * Front ACL Permission.
     */
    const FRONT_ACL_PERMISSION = ['FrontAcl_Permission::sample'];

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Action constructor.
     *
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Is action allowed?
     *
     * @return bool
     */
    public function isFrontClassAllowed(): bool
    {
        return true;
    }

    /**
     * Execute action.
     *
     * @return Page
     */
    public function execute(): Page
    {
        return $this->resultPageFactory->create();
    }
}
```

All these properties are also available for blocks, but you need to implement the **\DiZed\FrontAcl\Block\View\Element\AclBlockInterface** interface.

```php
<?php
namespace Name\Space\Sample\Block;

use DiZed\FrontAcl\Block\View\Element\AclBlockInterface;
use Magento\Framework\View\Element\Template;

class Sample extends Template implements AclBlockInterface
{
    /**
     * Front ACL Role.
     */
    const FRONT_ACL_ROLE = ['FrontAcl_Role::sample'];
    
    /**
     * Front ACL Permission.
     */
    const FRONT_ACL_PERMISSION = ['FrontAcl_Permission::sample'];

    /**
     * Is block allowed?
     *
     * @return bool
     */
    public function isFrontClassAllowed(): bool
    {
        return true;
    }
}
```

### Getting a collection of customers with specific roles and permissions.

Just need to use **\DiZed\FrontAcl\Helper\Data::getCustomerCollection** method instead of standard $this->customerCollectionFactory->create();

### Additional useful features.

- For independent work with roles and permissions in your own module: **\DiZed\FrontAcl\Api\RoleManagementInterface**.
- Get a list of available roles, permissions and some more features: **\DiZed\FrontAcl\Helper\Data**.
- Add work with permission constants to your own class: **\DiZed\FrontAcl\Helper\Traits\Acl**.
