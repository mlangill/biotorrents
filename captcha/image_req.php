<?php

// Echo the image - timestamp appended to prevent caching
echo '<a href="index.php" onclick="refreshimg(); return false;" title="Click to refresh image"><img class="cimage" src="captcha/GD_Security_image.php?' . time() . '" alt="Captcha image" /></a>';

?>