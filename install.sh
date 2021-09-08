#!/usr/bin/env bash

# Author: Dmitri Popov, dmpop@linux.com

#######################################################################
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#######################################################################

# Don't start as root
if [[ $EUID -eq 0 ]]; then
   echo "Run the script as a regular user"
   exit 1
fi

cd
sudo apt update
sudo apt full-upgrade -y
sudo apt update
sudo apt install -y git php-cli mplayer dialog
git clone https://github.com/dmpop/lilradio.git

# Create lilradio systemd unit
sudo sh -c "echo '[Unit]' > /etc/systemd/system/lilradio.service"
sudo sh -c "echo 'Description=web UI' >> /etc/systemd/system/lilradio.service"
sudo sh -c "echo '[Service]' >> /etc/systemd/system/lilradio.service"
sudo sh -c "echo 'Restart=always' >> /etc/systemd/system/lilradio.service"
sudo sh -c "echo 'ExecStart=/usr/bin/php -S 0.0.0.0:8000 -t /home/"$USER"/lilradio' >> /etc/systemd/system/lilradio.service"
sudo sh -c "echo 'ExecStop=/usr/bin/kill -HUP \$MAINPID' >> /etc/systemd/system/lilradio.service"
sudo sh -c "echo '[Install]' >> /etc/systemd/system/lilradio.service"
sudo sh -c "echo 'WantedBy=multi-user.target' >> /etc/systemd/system/lilradio.service"
sudo systemctl enable lilradio.service
sudo systemctl start lilradio.service

# Install pivumeter for Blinkt
dialog --clear \
    --title "pivumeter" \
    --backtitle "Little Radio" \
    --yesno "Install pivumeter for Blinkt?" 7 60

response=$?
case $response in
0)
    cd
    git clone https://github.com/pimoroni/pivumeter.git
    cd pivumeter
    ./setup.sh blinkt
    dialog --clear \
        --title "Reboot" \
        --msgbox "All done! Press OK to reboot the system." 5 45
    clear
    sudo reboot
    ;;
1)
    dialog --clear \
        --title "Reboot" \
        --msgbox "All done! Press OK to reboot the system." 5 45
    clear
    sudo reboot
    ;;
255)
    dialog --clear \
        --title "Reboot" \
        --msgbox "All done! Press OK to reboot the system." 5 45
    clear
    sudo reboot
    ;;
esac
