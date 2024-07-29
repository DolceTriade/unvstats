import unittest

from internals import log_parse

class TestRemoveColors(unittest.TestCase):

    def test_remove_colors(self):
        p = log_parse.Parser()
        print(p.Remove_colors('S^5ol^7idarity^5.^^^7Pk'))
        print(p.Remove_colors("^3D^2ol^5ce Tr^2i^3ade^5.^^^2Pk"))
        print(p.Remove_colors("^1Celestial ^4Rage^7.^^^8Pk"))
        print(p.Remove_colors("Rando name^*"))
        print(p.Remove_colors("^#ABcd3fRando name"))



if __name__ == '__main__':
    unittest.main()
