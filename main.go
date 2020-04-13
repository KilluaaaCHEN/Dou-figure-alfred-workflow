package main

import (
	"encoding/xml"
	"flag"
	"fmt"
	"github.com/PuerkitoBio/goquery"
	"io/ioutil"
	"log"
	"net/http"
	"os"
	"strings"
	"sync"
	"time"
)

const QUERY_URL = "https://www.doutula.com/search?keyword="
const IMG_PATH = "images"

var wg sync.WaitGroup

type Items struct {
	XMLName  xml.Name `xml:"items"`
	Version  string   `xml:"version,attr"`
	Encoding string   `xml:"encoding,attr"`
	Item     []Item   `xml:"item"`
}

type Item struct {
	XMLName      xml.Name `xml:"item"`
	Uid          int64    `xml:"uid,attr"`
	Arg          string   `xml:"arg,attr"`
	Valid        string   `xml:"valid,attr"`
	Icon         string   `xml:"icon"`
	Title        string   `xml:"title"`
	Autocomplete string   `xml:"autocomplete,attr"`
}

func GetXml(list []Item) string {
	bs := Items{Version: "1.0", Encoding: "UTF-8"}
	for _, v := range list {
		v.Uid = time.Now().UnixNano()
		v.Valid = "yes"
		bs.Item = append(bs.Item, v)
	}
	data, _ := xml.MarshalIndent(&bs, "", "  ")
	return string(data)
}

func getContent(query string) {
	url := QUERY_URL + query
	resp, err := http.Get(url)
	if err != nil {
		log.Fatal("网络请求失败:", err)
	}
	if resp.StatusCode != 200 {
		log.Fatalf("网络请求失败: %d %s", resp.StatusCode, resp.Status)
	}
	defer resp.Body.Close()

	doc, err := goquery.NewDocumentFromReader(resp.Body)
	if err != nil {
		log.Fatal(err)
	}
	list := make([]Item, 0)
	doc.Find(".img-responsive").Each(func(i int, s *goquery.Selection) {
		url, _ := s.Attr("data-original")
		name := s.Next().Text()
		icon := getFileName(url)
		wg.Add(1)
		go saveFile(url)
		list = append(list, Item{Arg: icon, Title: name, Icon: icon})
	})
	xmlStr := GetXml(list)
	wg.Wait()
	fmt.Println(xmlStr)
}

func exist(filename string) bool {
	_, err := os.Stat(filename)
	return err == nil || os.IsExist(err)
}

func getFileName(url string) string {
	url_list := strings.Split(url, "/")
	return IMG_PATH + "/" + url_list[len(url_list)-1]
}

func saveFile(url string) string {
	defer wg.Done()
	if !exist(IMG_PATH) {
		_ = os.Mkdir(IMG_PATH, 0777)
	}
	filename := getFileName(url)
	if exist(filename) {
		return ""
	}
	resp, _ := http.Get(url)
	defer resp.Body.Close()
	pix, _ := ioutil.ReadAll(resp.Body)

	if err := ioutil.WriteFile(filename, pix, 0777); err != nil {
		log.Fatal(err)
	}
	return filename
}

func main() {
	var query string
	flag.StringVar(&query, "query", "", "查询内容")
	flag.Parse()
	getContent(query)
}
