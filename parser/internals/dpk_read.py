# -*- coding: utf-8 -*-

import sys, os, re, zipfile
from PIL import Image
import StringIO
import tempfile

def dpkg_version_cmp(x, y):
	xp = 0
	yp = 0

	def order(c):
		if c.isdigit():
			return 0
		if c.isalpha():
			return ord(c)
		if c == '~':
			return -1
		return ord(c) + 256

	while xp < len(x) or yp < len(y):
		firstDiff = 0

		while (xp < len(x) and not x[xp].isdigit()) or (yp < len(y) and not y[yp].isdigit()):
			ac = order(x[xp]) if xp < len(x) else 0
			bc = order(y[yp]) if yp < len(y) else 0

			if ac != bc:
				return cmp(ac, bc)

			xp += 1
			yp += 1

		while xp < len(x) and x[xp] == '0':
			xp += 1
		while yp < len(y) and y[yp] == '0':
			yp += 1

		while (xp < len(x) and x[xp].isdigit()) and (yp < len(y) and y[yp].isdigit()):
			if firstDiff == 0:
				firstDiff = cmp((ord(x[xp]) if xp < len(x) else 0), (ord(y[yp]) if yp < len(y) else 0))
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
	""" Init Reader """
	def Main(self, dbc, Check_map_in_database, dpk_dir, one_dpk):
		# Regular expressions
		self.RE_FILESCAN = re.compile("^(levelshots|scripts)/(.+)\.(png|jpg|webp|tga|arena|crn)$")
		self.RE_ARENA    = re.compile("longname\s*\"(.*?)\"")

		# Localize parents function
		self.Check_map_in_database = Check_map_in_database

		# Internal data
		self.dbc     = dbc
		self.dpk_dir = dpk_dir

		# one dpk
		if one_dpk != None:
			if os.path.isfile(one_dpk) and one_dpk.endswith('.dpk'):
				print "Reading " + one_dpk + "..."
				self.Scan_dpk(one_dpk)
			else:
				print "file not found, or not a .dpk"
			return;

		# Check the directory
		if not os.path.isdir(self.dpk_dir):
			sys.exit("dpk directory does not exist")

		dpks = [i for i in os.listdir(self.dpk_dir) if re.match('^map-[^_]+_.*\.dpk$', i)]
		dpks.sort(cmp=lambda x,y: dpkg_version_cmp(x[:-4], y[:-4]))

		# Loop through all files
		for singlefile in dpks:
			# Check if file is ok
			if os.path.isfile(self.dpk_dir + '/' + singlefile) and re.match('^map-[^_]+_.*\.dpk$', singlefile):
				print "Reading " + singlefile + " ..."
				filepath = self.dpk_dir + '/' + singlefile
				self.Scan_dpk(filepath)

	""" Scan a single dpk file """
	def Scan_dpk(self, filename):
		try:
			dpk = zipfile.ZipFile(filename, 'r')
			namelist = dpk.namelist()
		except:
			print "Error while reading " + filename
			return

		for dpkfile in namelist:
			# Check the file
			match = self.RE_FILESCAN.search(dpkfile)
			if match == None:
				continue

			# The file is something we want, save it
			result    = match.groups()
			filetype  = result[0]
			mapname   = result[1]
			extension = result[2]

			if filetype == 'levelshots' and extension != 'arena':
				self.Save_levelshot(dpk, mapname, extension)
			elif filetype == 'scripts' and extension == 'arena':
				self.Save_mapname(dpk, mapname)

	""" Save a levelshot """
	def Save_levelshot(self, dpk, mapname, extension):
		data = dpk.read('levelshots/' + mapname + '.' + extension)
		srcimg = None
		dstimg = None
		tmpname = None

		try:
			if extension == 'webp':
				# We need to convert to PNG: use dwebp
				# This is expected to break on Windows (or maybe any non-POSIX)
				srcimg = tempfile.NamedTemporaryFile(suffix = '.webp')
				dstimg = tempfile.NamedTemporaryFile(suffix = '.png')

				srcimg.file.write(data)
				srcimg.file.flush()

				print 'running dwebp %s -o %s' % (srcimg.name, dstimg.name)
				ret = os.spawnlp(os.P_WAIT, 'dwebp', 'dwebp', srcimg.name, '-o', dstimg.name)
				if ret:
					raise Exception('dwebp returned %d' % ret)

				dstimg.file.seek(0)
				data = dstimg.file.read()
				# now we have PNG
			elif extension == 'crn':
				# We need to convert to PNG: use crunch
				# This is expected to break on Windows (or maybe any non-POSIX)
				# crunch actually replaces the output file, so work around that
				srcimg = tempfile.NamedTemporaryFile(suffix = '.crn')
				tmpname = srcimg.name[:-3] + 'png'
				srcimg.file.write(data)
				srcimg.file.flush()

				print 'running crunch -fileformat png -outsamedir %s' % (srcimg.name)
				ret = os.spawnlp(os.P_WAIT, 'crunch', 'crunch', '-fileformat', 'png', '-outsamedir', srcimg.name)
				if ret:
					raise Exception('crunch returned %d' % ret)

				with open(tmpname, 'rb') as tmpimg:
					data = tmpimg.read()
				# now we have PNG

			image = Image.open(StringIO.StringIO(data))
			image.thumbnail((256, 144), Image.BICUBIC)
			levelshot = StringIO.StringIO()
			image.save(levelshot, 'JPEG')
			levelshot_string = levelshot.getvalue()
		except Exception as e:
			print "Error while processing levelshot %s.%s: %s" % (mapname, extension, e)
			if srcimg != None:
				srcimg.file.close()
			if dstimg != None:
				dstimg.file.close()
			if tmpname != None:
				os.unlink(tmpname)
			return

		if srcimg != None:
			srcimg.file.close()
		if dstimg != None:
			dstimg.file.close()
		if tmpname != None:
			os.unlink(tmpname)

		# Image thumbnail created, insert it into database
		map_id    = self.Check_map_in_database(mapname)

		self.dbc.execute("UPDATE `maps` SET `map_levelshot` = %s WHERE map_id = %s", (levelshot_string, map_id))


	""" Save a mapname """
	def Save_mapname(self, dpk, mapname):
		data = dpk.read('scripts/' + mapname + '.arena')

		# Check the data
		match = self.RE_ARENA.search(data)
		if match == None:
			return

		# The data contains a longname, put it into the database
		result    = match.groups()
		longname  = result[0]
		map_id    = self.Check_map_in_database(mapname)

		self.dbc.execute("UPDATE `maps` SET `map_longname` = %s WHERE map_id = %s", (longname, map_id))
