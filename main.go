package main

import (
	"encoding/json"
	"encoding/xml"
	"flag"
	"fmt"
	"io/ioutil"
	"log"
	"net/http"
	"os"
	"strings"
	"sync"
	"time"
)

const ApiUrl = "https://www.52doutu.cn/api/"
const ImgPath = "images"

var wg sync.WaitGroup

type Result struct {
	Code  int    `json:"code"`
	Msg   string `json:"msg"`
	Count int    `json:"count"`
	Rows  []struct {
		URL string `json:"url"`
	} `json:"rows"`
}

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

func showError(msg string) {
	list := make([]Item, 0)
	list = append(list, Item{Arg: "", Title: "异常:" + msg, Icon: ""})
	xmlStr := GetXml(list)
	wg.Wait()
	fmt.Println(xmlStr)
	os.Exit(1)
}

func getContent(query string) {
	client := &http.Client{}

	req, err := http.NewRequest("POST", ApiUrl, strings.NewReader("types=search&action=searchpic&limit=60&offset=0&wd="+query))
	if err != nil {
		showError(err.Error())
	}

	req.Header.Set("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8")
	req.Header.Set("Accept", "application/json, text/javascript, */*; q=0.01")
	req.Header.Set("User-Agent", "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36")
	req.Header.Set("Referer", ApiUrl)
	req.Header.Set("Origin", ApiUrl)

	resp, err := client.Do(req)

	if err != nil {
		showError(err.Error())
	}

	defer resp.Body.Close()

	body, err := ioutil.ReadAll(resp.Body)
	if err != nil {
		showError(err.Error())
	}
	rst := Result{}

	if err := json.Unmarshal(body, &rst); err != nil {
		showError(err.Error())
	}

	if rst.Code != 200 {
		showError(rst.Msg)
	}
	if rst.Count <= 0 {
		showError("无结果")
	}

	list := make([]Item, 0)
	for _, v := range rst.Rows {
		url := v.URL
		icon := getFileName(url)
		wg.Add(1)
		go saveFile(url)
		list = append(list, Item{Arg: icon, Title: query, Icon: icon})
	}
	xmlStr := GetXml(list)
	wg.Wait()
	fmt.Println(xmlStr)
}

func exist(filename string) bool {
	_, err := os.Stat(filename)
	return err == nil || os.IsExist(err)
}

func getFileName(url string) string {
	urlList := strings.Split(url, "/")
	return ImgPath + "/" + urlList[len(urlList)-1]
}

func saveFile(url string) string {
	defer wg.Done()
	if !exist(ImgPath) {
		_ = os.Mkdir(ImgPath, 0777)
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
