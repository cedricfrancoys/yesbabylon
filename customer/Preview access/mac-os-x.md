# Access Preview instance undex Mac OS X

Follow the instructions below to edit your hosts file if you are running Mac OS X.

## Edit Hosts file

### A. Use a host file editor

The tool [Hosts System preference pane](https://www.macupdate.com/app/mac/40003/hosts) allows you to easily update your hosts file in a very intuitive way.
![img](https://screenshots.macupdate.com/JPG/40003/40003_scr.jpg)




### B. Edit the host file with command line

1. Open the Terminal application. Start by typing Terminal on the Spotlight or by going to : **Applications** > **Utilities** > **Terminal**.

2. Open the hosts file by typing in the Terminal that you have just opened:
   ```
   sudo nano /private/etc/hosts
   ```
   **Note**: Some versions of Mac OS X will lock permissions on the hosts file (the file is marked as immutable). In the event this happens, use the following command instead: 
   ```
   sudo chflags nouchg /private/etc/host
   ```

3. Type your user password when prompted.

4. Do the required changes.

5. When done editing the hosts file, press Control-o to save the file.

6. Press Enter on the filename prompt, and Control-x to exit the editor.

## DNS Flush

As a general rule, every time the hosts file is modified, it is recommended that you flush your DNS so that the new changes can be implemented more swiftly. 

On most OS, rebooting the computer is the easiest way. However, DNS flushing can also be done manually using command line:

```
sudo dscacheutil -flushcache
sudo killall -HUP mDNSResponder
```