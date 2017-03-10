###########################################################################

These files are specifically designed for use with the OS Ticket App[1] by
Atomic Computers and Design, LLC in conjunction with the OS Ticket Support
Ticket System[2] by Enhancesoft.  Currently, the integration was made
specifically for OS Ticket versions 1.8.3 and 1.9.1.  However, the
integration should also work with version 1.7.*, except for the Push
Notifications.

[1] http://osticketapp.com
[2] http://osticket.com

###########################################################################


This downloads includes a soap API folder and files, and a replacement file
for the ticket class.

Firstly, you will want to go to your OS Ticket install's root folder.
There, you will see a folder "api".  Inside this folder, plase the "soap"
folder and it's contents (including the "soap" folder itself).

At this point, your file system should look similar to:
http://domain.com/osticketroot/api/soap/...

These soap files is how the app will be able to pull it's data.  You will
not edit any of these files yet, but will edit the "notify.php" file later
if you wish to receive push notifications.

After the files have been uploaded, log into the app using your OS Ticket
URL and account login credentials.  (Your login credentials are not
stored on our servers - only in the app on your personal phone.)  At this
point, you should be able to access all the tickets, reply, assign,
close, re-open, and create new tickets, all from your mobile app.

If you also want to receive push notifications, there are just 2 more simple
steps.  First, from the files contained into this download, enter the folder
for the version of OS Ticket you are running and find the class.ticket.php
file.  You will need to replace your OS Ticket's file with this one at
http://domain.com/osticketroom/includes/class.ticket.php .  Be sure to first
backup your original file just in case.  Then replace your file with this
one.

Next, go to the "Settings" tab in the app and find your integration ID.  In
the "soap" folder, you will need to edit the "notify.php" file.  Replace
the hashes currently set as the integration ID with the one shown in your
app.  This will allow your server to send push notifications.  What
notifications are pushed?  Any notification that you would normally be
emailed about will be pushed.  New ticket, ticket replies, ticket assignments,
etc.  However, in your app's settings, you also have the option to turn off
different types of notifications in case you want to receive replies, but not
new ticket notifications.

###########################################################################

If you need help or have questions, you may contact Atomic Computers and
Design, LLC at http://atomicx.com/contact.php