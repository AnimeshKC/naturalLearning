import sys, json, re, string, functools, operator
#data = sys.argv
#print (json.dumps(data))



def removeBetweenCharacters(a,b,data):
    regexString = a + '[^'+ b + ']+'+b
    #print(regexString)
    my_regex = re.sub(a + '[^'+ b + ']+'+b, ' ', data)
    return my_regex



def wordListToFreqDict(wordlist):
    wordfreq = [wordlist.count(p) for p in wordlist]
    firstDict = dict(zip(wordlist,wordfreq))
    #the plan to make the key word filter 5 occurances
    #right now, use 50 for simplicity
    
    filteredDict = {}
    for k,v in firstDict.items():
        if v >= 50:
            filteredDict[k] = v
    return filteredDict

def sortFreqDict(freqdict):
    aux = [(freqdict[key], key) for key in freqdict]
    aux.sort()
    aux.reverse()
    return aux


def split_into_sentences(text):
    alphabets= "([A-Za-z])"
    prefixes = "(Mr|St|Mrs|Ms|Dr)[.]"
    suffixes = "(Inc|Ltd|Jr|Sr|Co)"
    starters = "(Mr|Mrs|Ms|Dr|He\s|She\s|It\s|They\s|Their\s|Our\s|We\s|But\s|However\s|That\s|This\s|Wherever)"
    acronyms = "([A-Z][.][A-Z][.](?:[A-Z][.])?)"
    websites = "[.](com|net|org|io|gov)"
    text = " " + text + "  "

    #remove additional whitespaces
    text = text.replace("\n"," ")
    text = text.replace("\r"," ")
    text = text.replace("\s"," ")
    text = text.replace("\t"," ")

    
    
    if "Ph.D" in text: text = text.replace("Ph.D.","Ph<prd>D<prd>")
    text = re.sub("\s" + alphabets + "[.] "," \\1<prd> ",text)
    text = re.sub(acronyms+" "+starters,"\\1<stop> \\2",text)
    text = re.sub(alphabets + "[.]" + alphabets + "[.]" + alphabets + "[.]","\\1<prd>\\2<prd>\\3<prd>",text)
    text = re.sub(alphabets + "[.]" + alphabets + "[.]","\\1<prd>\\2<prd>",text)
    text = re.sub(" "+suffixes+"[.] "+starters," \\1<stop> \\2",text)
    text = re.sub(" "+suffixes+"[.]"," \\1<prd>",text)
    text = re.sub(" " + alphabets + "[.]"," \\1<prd>",text)
    text = re.sub(prefixes,"\\1<prd>",text)
    text = re.sub(websites,"<prd>\\1",text)
    text = re.sub(' +', ' ', text)
    if "”" in text: text = text.replace(".”","”.")
    if "\"" in text: text = text.replace(".\"","\".")
    if "!" in text: text = text.replace("!\"","\"!")
    if "?" in text: text = text.replace("?\"","\"?")
    text = text.replace(".",".<stop>")
    text = text.replace("?","?<stop>")
    text = text.replace("!","!<stop>")
    text = text.replace("<prd>",".")
    sentences = text.split("<stop>")
    sentences = sentences[:-1]
    sentences = [s.strip() for s in sentences]
    return sentences

def getData(filename):
    with open(filename) as json_file:
        data = json.load(json_file)
        rawData = []
        wordList = []
        sentenceList = []
        for i in range(len(data)):

            #Clean up non-text
            clean = data[i]
            clean = removeBetweenCharacters('<','>',clean)
            clean = removeBetweenCharacters('&',';',clean)
            clean = removeBetweenCharacters('MATH','.',clean)
            unpunctuated = clean.split()
            sentences = split_into_sentences(clean)
            sentenceList.append(sentences)
            wordList.append(unpunctuated)
            rawData.append(clean)

        #print(sentenceList[0])

        mergedWordList = functools.reduce(operator.iconcat, wordList, [])
        #print(len(mergedWordList))

        stopWords = ['a', 'the', 'and', 'at', 'an', 'all', 'again' 'above', 'for',
                     'any', 'are', 'because', 'as', 'but', 'do', 'did', 'done', 'from',
                     'had', 'has', 'having', 'of', 'in', 'to', 'on', 'exercises',
                     'data','or', 'your', 'solutions', 'is', 'you', 'that', 'this' 'given',
                     'section', 'be', 'can', 'four', 'each', 'use', 'top', 'student', 'sample', 'provided',
                     'after', 'read', 'page', 'completed', 'complete', 'completing', 'with',
                     'should', 'section', 'readings', 'question', 'this', 'given', 'pages',
                     'will', 'reading','about', '\'', 'x', 'chapter', '(', ')',
                     'above', 'by', 'may', '-value', 'define', 'not', 'using', 'sections', 'page.', 'what', 'we', 'two', 'three',
                     '=', 'b.', 'a.', 'c.', '1', '2', '3', '4', '5', '6',
                     '7','8', '9', '0', '10',  'age', 'listed', 'below', 'section', 'exercise',
                     'its', '.', 's', 'h', 'y', 'b', '1', 'per',
                     'below', 'manual', 'practice', 'it', 'able', 'our', 'work', 'unit', 'p', '2', ',']
        filteredWordList = []
        for w in mergedWordList:
            #removing trailing periods
            if  w.lower() not in stopWords and len(w.lower())>1:

                if w.lower()[-1] == '.':
                    if len(w.lower()[:-1]) >1:
                        filteredWordList.append(w.lower()[:-1])
                else:
                    filteredWordList.append(w.lower())
        #print(len(filteredWordList))
        wordDict = wordListToFreqDict(filteredWordList)
        wordDict = sortFreqDict(wordDict)
        finalList = []
        for i in range(len(wordDict)):
            tempList = []
            for j in range(len(sentenceList)):
                for k in range(len(sentenceList[j])):
                    if wordDict[i][1] in sentenceList[j][k]:
                        tempList.append([j,k])
            finalList.append([wordDict[i][0],wordDict[i][1],tempList])        
        
        

        
    return [rawData, mergedWordList, sentenceList, wordDict, finalList]

    
currentData = getData("dataArray.json")
rawData = currentData[0]
wordData = currentData[1]
sentenceData = currentData[2]
wordDictData = currentData[3]
connectionsData = currentData[4]
    

#print(sentenceData[0])
#print(rawData[0])
#print (wordData[0:100])
#print (wordDictData)
#print (connectionsData[0:5])

#construct some sort of connection based on the frequency and sentences


class Vertex:
    def __init__(self, node):
        self.id = node
        self.adjacent = {}

    def __str__(self):
        return str(self.id) + ' adjacent: ' + str([x.id for x in self.adjacent])

    def add_neighbor(self, neighbor, weight=0):
        self.adjacent[neighbor] = weight

    def get_connections(self):
        return self.adjacent.keys()  

    def get_id(self):
        return self.id

    def get_weight(self, neighbor):
        return self.adjacent[neighbor]

class Graph:
    def __init__(self):
        self.vert_dict = {}
        self.num_vertices = 0

    def __iter__(self):
        return iter(self.vert_dict.values())

    def add_vertex(self, node):
        self.num_vertices = self.num_vertices + 1
        new_vertex = Vertex(node)
        self.vert_dict[node] = new_vertex
        return new_vertex

    def get_vertex(self, n):
        if n in self.vert_dict:
            return self.vert_dict[n]
        else:
            return None

    def add_edge(self, frm, to, cost = 0):
        if frm not in self.vert_dict:
            self.add_vertex(frm)
        if to not in self.vert_dict:
            self.add_vertex(to)

        self.vert_dict[frm].add_neighbor(self.vert_dict[to], cost)
        self.vert_dict[to].add_neighbor(self.vert_dict[frm], cost)

    def get_vertices(self):
        return self.vert_dict.keys()

def generateGraph(connectionsList):
    g = Graph()

    associationList = []
    maxCount = 0
    for i in range(len(connectionsList)):
        currentWord = connectionsList[i][1]
        g.add_vertex(currentWord)
        if len(connectionsList)-1 > i:
            #iterate through the words after the current word
            for j in range(i+1,len(connectionsList)):
                matchCount = 0
                nextWord = connectionsList[j][1]
                for location in connectionsList[i][2]:
                    if location in connectionsList[j][2] and currentWord not in nextWord and nextWord not in currentWord:
                        matchCount += 1
                        if matchCount > maxCount:
                            maxCount = matchCount
                if matchCount >0:
                    associationList.append([currentWord, nextWord, matchCount])
    #sort from most frequent to least frequent
    associationList = sorted(associationList, key=operator.itemgetter(2), reverse = True)
    #print (associationList[0:10])
    #print(maxCount)
    refVal = maxCount +1
    for i in range(len(associationList)):
        firstWord = associationList[i][0]
        secondWord = associationList[i][1]
        associationList[i][2] = refVal - associationList[i][2]
        weight = associationList[i][2]
        g.add_edge(firstWord,secondWord, weight)
    return associationList
    

associationList = generateGraph(connectionsData)

outputArray = [wordDictData, associationList]
#print (outputArray)
#print (associationList[0:100])

print(json.dumps(outputArray))                
                
            
            

        

'''
a = 'True'
if a == 'True':

    g = Graph()

    g.add_vertex('a')
    g.add_vertex('b')
    g.add_vertex('c')
    g.add_vertex('d')
    g.add_vertex('e')
    g.add_vertex('f')

    g.add_edge('a', 'b', 7)  
    g.add_edge('a', 'c', 9)
    g.add_edge('a', 'f', 14)
    g.add_edge('b', 'c', 10)
    g.add_edge('b', 'd', 15)
    g.add_edge('c', 'd', 11)
    g.add_edge('c', 'f', 2)
    g.add_edge('d', 'e', 6)
    g.add_edge('e', 'f', 9)

    for v in g:
        for w in v.get_connections():
            vid = v.get_id()
            wid = w.get_id()
            print '( %s , %s, %3d)'  % ( vid, wid, v.get_weight(w))

    for v in g:
        print 'g.vert_dict[%s]=%s' %(v.get_id(), g.vert_dict[v.get_id()])'''

        

