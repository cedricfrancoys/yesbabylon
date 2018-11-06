# Access Preview instance under Windows 7

Follow the instructions below to edit your hosts file if you are running Windows Vista or Windows 7.

## Edit Hosts file

### A. Use a host file editor

The tool [Hosts File Editor](https://scottlerch.github.io/HostsFileEditor/) allows you to easily update your hosts file in a very intuitive way.

![img](https://cloud.githubusercontent.com/assets/1789883/24075121/a68ddcc8-0bd2-11e7-9eed-c53d02a08930.png)


### B. Edit the host file with notepad


1. Browse to **Start** > **All Programs** > **Accessories**.
2. Right-click **Notepad**, and select **Run as administrator** (Click **Continue** on the UAC prompt)
3. Click **File** > **Open**.
4. Browse to `C:\Windows\System32\Drivers\etc`.
5. Change the file filter drop-down box from **Text Documents** (`*.txt`) to **All Files** (`*.*`)
6. Select `hosts`, and click **Open**.
7. Make the needed changes, and close Notepad.
8. **Save** when prompted.



## DNS Flush

As a general rule, every time the hosts file is modified, it is recommended that you flush your DNS so that the new changes can be implemented more swiftly. 

On most OS, rebooting the computer is the easiest way. However, DNS flushing can also be done manually using command line:

```
ipconfig /flushdns
```