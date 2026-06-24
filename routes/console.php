<?php

Schedule::command('telescope:prune')->daily();
Schedule::command('otp:clean')->daily();
