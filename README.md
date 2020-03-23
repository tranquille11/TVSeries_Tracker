# TVSeries_Tracker

1. Run `composer install` to enable dependencies.
2. Change `seriale.txt` as you see fit.
3. Set up CronJob to have the script run automatically.
4. Open terminal and run the following commands:
- `crontab -e`
- If you have never used Cron before, you will be asked to name a default editor. Choose Nano.
- Type the following in the editor: `@reboot sleep 60 && php /home/your_username/TVSeries_Tracker
/script.php && mv /home/your_username/seriale.xlsx /path-to/personal/folder`
- Press `CTRL+S & CTRL+X`
- If you see message `crontab: installing new crontab`, you have succesfully added the file to
 the scripts run at boot-up.