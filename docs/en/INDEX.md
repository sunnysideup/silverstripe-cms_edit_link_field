Create a form field that links to any object:
```php
CMSEditLinkField::create(
      $fieldName = 'Parent',
      $title = 'Edit my parent',
      $linkedObject = $this->MyParent()
 );
 
 ```
