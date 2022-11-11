# check_systemd
Nagios check script to count systemd units by state and alert

## Requires
Nothing special

## Setup
Download check_systemd zip file or clone it into your target server, then
```$ php composer.phar install```
```$ php composer.phar dump-autoload -o```

and call it remotely with `check_by_ssh`

## Usage
Nagios configuration:
```
define command {
        command_name    check_by_ssh_check_systemd
        command_line    $USER1$/check_by_ssh -H $HOSTADDRESS$ -p 143 -C "php /home/nagios/check_systemd/check_systemd.php"
}

define service {
        use                             generic-service
        host_name                       myhost
        service_description             my-service
        check_command                   check_systemd
}
```

## Checks performed
Currently, checks if there are failed units and reports a critical status when there is at least 1.
