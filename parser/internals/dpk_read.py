# -*- coding: utf-8 -*-

import sys, os, re, zipfile
from PIL import Image
import io
import tempfile
import functools


def cmp(a, b):
    return (a > b) - (a < b)


def dpkg_version_cmp(x, y):
    xp = 0
    yp = 0

    def order(c):
        if c.isdigit():
            return 0
        if c.isalpha():
            return ord(c)
        if c == "~":
            return -1
        return ord(c) + 256

    while xp < len(x) or yp < len(y):
        firstDiff = 0

        while (xp < len(x) and not x[xp].isdigit()) or (
            yp < len(y) and not y[yp].isdigit()
        ):
            ac = order(x[xp]) if xp < len(x) else 0
            bc = order(y[yp]) if yp < len(y) else 0

            if ac != bc:
                return cmp(ac, bc)

            xp += 1
            yp += 1

        while xp < len(x) and x[xp] == "0":
            xp += 1
        while yp < len(y) and y[yp] == "0":
            yp += 1

        while (xp < len(x) and x[xp].isdigit()) and (yp < len(y) and y[yp].isdigit()):
            if firstDiff == 0:
                firstDiff = cmp(
                    (ord(x[xp]) if xp < len(x) else 0),
                    (ord(y[yp]) if yp < len(y) else 0),
                )
            xp += 1
            yp += 1

        if xp < len(x) and x[xp].isdigit():
            return 1
        if yp < len(y) and y[yp].isdigit():
            return -1
        if firstDiff:
            return firstDiff

    return 0


""" Class: Reader """


class Reader:
    """Init Reader"""

    def Main(self, dbc, Check_map_in_database, dpk_dir, one_dpk):
        # Regular expressions
        self.RE_FILESCAN = re.compile(
            R"^meta/([^/]+)/(.+)\.(png|jpg|webp|tga|arena|crn)$"
        )
        self.RE_ARENA = re.compile(R'longname\s*"(.*?)"')

        # Localize parents function
        self.Check_map_in_database = Check_map_in_database

        # Internal data
        self.dbc = dbc
        if isinstance(dpk_dir, str):
            self.dpk_dirs = [dpk_dir]
        else:
            self.dpk_dirs = list(dpk_dir)

        # one dpk
        if one_dpk != None:
            if os.path.isfile(one_dpk) and one_dpk.endswith(".dpk"):
                print("Reading " + one_dpk + "...")
                self.Scan_dpk(one_dpk)
            else:
                print("file not found, or not a .dpk")
            return

        if not self.dpk_dirs:
            sys.exit("no dpk directories configured")

        dpks = []
        for dpk_dir in self.dpk_dirs:
            if not os.path.isdir(dpk_dir):
                sys.exit("dpk directory does not exist: " + dpk_dir)

            for singlefile in os.listdir(dpk_dir):
                if not re.match(R"^map-[^_]+_.*\.dpk$", singlefile):
                    continue

                filepath = os.path.join(dpk_dir, singlefile)
                if os.path.isfile(filepath):
                    dpks.append(filepath)

        dpks.sort(
            key=functools.cmp_to_key(
                lambda x, y: dpkg_version_cmp(
                    os.path.basename(x)[:-4], os.path.basename(y)[:-4]
                )
            )
        )

        for filepath in dpks:
            print("Reading " + filepath + " ...")
            self.Scan_dpk(filepath)

    """ Scan a single dpk file """

    def Scan_dpk(self, filename):
        try:
            dpk = zipfile.ZipFile(filename, "r")
            namelist = dpk.namelist()
        except:
            print("Error while reading " + filename)
            return

        for dpkfile in namelist:
            # Check the file
            match = self.RE_FILESCAN.search(dpkfile)
            if match == None:
                continue

            # The file is something we want, save it
            result = match.groups()
            mapdir = result[0]
            mapname = result[1]
            extension = result[2]

            if mapname == mapdir and extension != "arena":
                self.Save_levelshot(dpk, mapname, extension)
            elif extension == "arena":
                self.Save_mapname(dpk, mapname)

    """ Save a levelshot """

    def Save_levelshot(self, dpk, mapname, extension):
        data = dpk.read("meta/%s/%s.%s" % (mapname, mapname, extension))
        srcimg = None
        dstimg = None
        tmpname = None

        try:
            img = None
            if extension == "webp":
                # We need to convert to PNG: use dwebp
                # This is expected to break on Windows (or maybe any non-POSIX)
                srcimg = tempfile.NamedTemporaryFile(suffix=".webp")
                dstimg = tempfile.NamedTemporaryFile(suffix=".png")

                srcimg.file.write(data)
                srcimg.file.flush()

                print("running dwebp %s -o %s" % (srcimg.name, dstimg.name))
                ret = os.spawnlp(
                    os.P_WAIT, "dwebp", "dwebp", srcimg.name, "-o", dstimg.name
                )
                if ret:
                    raise Exception("dwebp returned %d" % ret)

                img = dstimg.name
                # now we have PNG
            elif extension == "crn":
                # We need to convert to PNG: use crunch
                # This is expected to break on Windows (or maybe any non-POSIX)
                # crunch actually replaces the output file, so work around that
                srcimg = tempfile.NamedTemporaryFile(suffix=".crn")
                tmpname = srcimg.name[:-3] + "png"
                srcimg.file.write(data)
                srcimg.file.flush()

                print("running crunch -fileformat png -outsamedir %s" % (srcimg.name))
                ret = os.spawnlp(
                    os.P_WAIT,
                    "crunch",
                    "crunch",
                    "-fileformat",
                    "png",
                    "-outsamedir",
                    srcimg.name,
                )
                if ret:
                    raise Exception("crunch returned %d" % ret)
                img = tmpname
                # now we have PNG
            else:  # Hope that its something Pillow can read
                dstimg = tempfile.NamedTemporaryFile(suffix="." + extension)
                dstimg.file.write(data)
                dstimg.file.flush()
                img = dstimg.name

            print(img)
            image = Image.open(str(img))
            image.thumbnail((256, 144), Image.BICUBIC)
            levelshot = io.BytesIO()
            image.save(levelshot, "JPEG")
            levelshot_string = levelshot.getvalue()
        except Exception as e:
            print(
                "Error while processing levelshot %s.%s" % (mapname, extension)
            )
            if srcimg != None:
                srcimg.file.close()
            if dstimg != None:
                dstimg.file.close()
            if tmpname != None:
                os.unlink(tmpname)
            raise e

        if srcimg != None:
            srcimg.file.close()
        if dstimg != None:
            dstimg.file.close()
        if tmpname != None:
            os.unlink(tmpname)

        # Image thumbnail created, insert it into database
        map_id = self.Check_map_in_database(mapname)

        self.dbc.execute(
            "UPDATE `maps` SET `map_levelshot` = %s WHERE map_id = %s",
            (levelshot_string, map_id),
        )

    """ Save a mapname """

    def Save_mapname(self, dpk, mapname):
        data = dpk.read("meta/%s/%s.arena" % (mapname, mapname))

        # Check the data
        match = self.RE_ARENA.search(data.decode())
        if match == None:
            return

        # The data contains a longname, put it into the database
        result = match.groups()
        longname = result[0]
        map_id = self.Check_map_in_database(mapname)

        self.dbc.execute(
            "UPDATE `maps` SET `map_longname` = %s WHERE map_id = %s",
            (longname, map_id),
        )
