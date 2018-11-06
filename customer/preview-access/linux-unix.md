# Access Preview instance under Linux/Unix

Follow the instructions below to edit your hosts file if you are running a distribution of Linux or Unix.

## Edit Hosts file

### A. Use a host file editor

The tool [Hosts Switcher](https://code.google.com/archive/p/host-switcher/) allows you to easily update your hosts file in a very intuitive way.

![img](http://image.ganjistatic1.com/gjfs02/M02/69/CF/wKhzR07y8rWlWTyzAABBWA7SC0Y559_450-0_8-5.jpg)

### B. Edit the host file with command line

On Unix-based systems, you can find the hosts file at `/etc/hosts`. 

1. Open a terminal:
    * Go to **Menu**
    * Select **Applications ** > **Accessories** > **Terminal**


2. Open the hosts file by typing in the Terminal that you have just opened:

   ```
   sudo vi /etc/hosts
   ```

3. Do the required changes.

4. When done editing the hosts file, type `:wq` to save and close the file.





## DNS Flush

As a general rule, every time the hosts file is modified, it is recommended that you flush your DNS so that the new changes can be implemented more swiftly. 

On most OS, rebooting the computer is the easiest way. However, DNS flushing can also be done manually using command line:

```
sudo /etc/init.d/dns-clean restart
```