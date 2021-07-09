// +build !windows

/*
** Zabbix
** Copyright (C) 2001-2021 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

package file

import (
	"fmt"
	"io/fs"
	"os/user"
	"strconv"
	"syscall"
	"time"
)

func getFileInfo(info *fs.FileInfo, name string) (fileinfo *fileInfo, err error) {
	var fi fileInfo

	stat := (*info).Sys().(*syscall.Stat_t)
	if stat == nil {
		return nil, fmt.Errorf("Cannot obtain %s permission information", name)
	}

	perm := fmt.Sprintf("%04o", stat.Mode&07777)
	fi.Permissions = &perm

	u := strconv.FormatUint(uint64(stat.Uid), 10)
	usr, err := user.LookupId(u)
	if err != nil {
		return nil, fmt.Errorf("Cannot obtain %s user information: %s", name, err)
	}
	fi.User = usr.Name
	fi.Uid = &stat.Uid

	g := strconv.FormatUint(uint64(stat.Gid), 10)
	group, err := user.LookupGroupId(g)
	if err != nil {
		return nil, fmt.Errorf("Cannot obtain %s group information: %s", name, err)
	}
	fi.Group = &group.Name
	fi.Gid = &stat.Gid

	fi.Time.Access = jsTimeLoc(time.Unix(stat.Atim.Unix()))
	fi.Time.Change = jsTimeLoc(time.Unix(stat.Ctim.Unix()))

	return &fi, nil
}
