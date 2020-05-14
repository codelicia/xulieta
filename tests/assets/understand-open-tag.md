
```php
$code = 'echo "Hello";';

eval('<?php ' . $code); // this line is buggy due to "<?php "
```
