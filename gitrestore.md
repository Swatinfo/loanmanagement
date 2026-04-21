# Git restore — pre-newtheme code

To reset a file back to pre-newtheme state:

```
git checkout 6176333 -- .ignore/old_code_backup/<path-you-want>
```

Or to browse the old tree:

```
git show 6176333:.ignore/old_code_backup/resources/views/dashboard.blade.php
```
