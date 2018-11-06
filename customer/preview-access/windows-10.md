# Access Preview instance under Windows 10

Follow the instructions below to edit your hosts file if you are running Windows 8 or Windows 10.

## Edit Hosts file

### A. Use a host file editor

The tool [Hosts File Editor](https://scottlerch.github.io/HostsFileEditor/) allows you to easily update your hosts file in a very intuitive way.

![img](https://cloud.githubusercontent.com/assets/1789883/24075121/a68ddcc8-0bd2-11e7-9eed-c53d02a08930.png)


### B. Edit the host file with notepad

1. Select the **Start** key and locate Notepad. (If you do not see it on your current Start page, begin typing “Notepad” and a search box will appear on the right side of the screen with a list of programs under it. Notepad should be at the top of this list.
2. **Right click** on **Notepad**. You will see options appear on the bottom portion of the Start Page.
3. Select **Run as administrator**. 

> Note: Performing this action may cause Windows User Account Control to prompt you with a warning or, if you are logged in as another user, a request for the Administrator password. This step is necessary to modify system files such as the hosts file.

1. Click **File** in the menu bar at the top of Notepad and select Open.
2. Click the dropdown box in the lower right hand corner that is set to **Text Documents** (`*.txt`) and select **All Files** (`*.*`)
3. Browse to `C:\Windows\System32\Drivers\etc` and open the `hosts` file.
4. Make the needed changes, as shown above, and close Notepad.
5. Save when prompted.



## DNS Flush

As a general rule, every time the hosts file is modified, it is recommended that you flush your DNS so that the new changes can be implemented more swiftly. 

On most OS, rebooting the computer is the easiest way. However, DNS flushing can also be done manually using command line:

```
ipconfig /flushdns
```